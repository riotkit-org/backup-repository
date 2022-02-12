//
// See: https://golangcode.com/argon2-password-hashing/
// Thanks to Edd Turtle
//

package security

import (
	"crypto/rand"
	"crypto/sha256"
	"crypto/subtle"
	"encoding/base64"
	"encoding/hex"
	"errors"
	"fmt"
	"github.com/sirupsen/logrus"
	"golang.org/x/crypto/argon2"
	"runtime"
	"strings"
)

type Argon2Config struct {
	time    uint32
	memory  uint32
	threads uint8
	keyLen  uint32
}

func CreateDefaultPasswordConfig() *Argon2Config {
	return &Argon2Config{
		time:    1,
		memory:  64 * 1024,
		threads: 4,
		keyLen:  32,
	}
}

// CreateHashFromPassword is used to generate a new password hash for storing and
// comparing at a later date.
func CreateHashFromPassword(password string) (string, error) {
	// Generate a Salt
	salt := make([]byte, 16)
	if _, err := rand.Read(salt); err != nil {
		return "", err
	}

	c := CreateDefaultPasswordConfig()
	hash := argon2.IDKey([]byte(password), salt, c.time, c.memory, c.threads, c.keyLen)

	// Base64 encode the salt and hashed password.
	b64Salt := base64.StdEncoding.EncodeToString(salt)
	b64Hash := base64.StdEncoding.EncodeToString(hash)

	format := "$argon2id$v=%d$m=%d,t=%d,p=%d$%s$%s"
	full := fmt.Sprintf(format, argon2.Version, c.memory, c.time, c.threads, b64Salt, b64Hash)

	runtime.GC()

	return base64.StdEncoding.EncodeToString([]byte(full)), nil
}

// ComparePassword is used to compare a user-inputted password to a hash to see
// if the password matches or not.
func ComparePassword(password string, hash string) (bool, error) {
	hashByte, _ := base64.StdEncoding.DecodeString(hash)
	hash = string(hashByte)
	parts := strings.Split(hash, "$")

	if len(parts) < 3 {
		logrus.Warning("Password format is invalid. To properly encode a password use `backup-repository " +
			"--encode-password='your-password'` and put it in `kind: Secret` or in `kind: BackupUser`")

		return false, errors.New("invalid password hash format. Check `kind: Secret` or `kind: BackupUser`")
	}

	c := &Argon2Config{}
	_, err := fmt.Sscanf(parts[3], "m=%d,t=%d,p=%d", &c.memory, &c.time, &c.threads)
	if err != nil {
		logrus.Errorf("Cannot unpack password hash for parameters recognition. Invalid format")
		return false, err
	}

	salt, err := base64.StdEncoding.DecodeString(parts[4])
	if err != nil {
		logrus.Errorf("Cannot decode salt. Check if it is a valid base64 string" +
			" (salt is base64 encoded part inside base64 encoded secret - 4th position)")

		return false, err
	}

	decodedHash, err := base64.StdEncoding.DecodeString(parts[5])
	if err != nil {
		logrus.Errorf("Cannot decode 5th part of password hash, which is a password token")
		return false, err
	}

	c.keyLen = uint32(len(decodedHash))
	comparisonHash := argon2.IDKey([]byte(password), salt, c.time, c.memory, c.threads, c.keyLen)
	runtime.GC()

	logrus.Debugf("Comparing passwords...")
	return subtle.ConstantTimeCompare(decodedHash, comparisonHash) == 1, nil
}

func HashJWT(jwt string) string {
	asByte := sha256.Sum256([]byte(jwt))

	return hex.EncodeToString(asByte[:])
}
