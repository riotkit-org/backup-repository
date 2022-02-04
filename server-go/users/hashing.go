//
// See: https://golangcode.com/argon2-password-hashing/
// Thanks to Edd Turtle
//

package users

import (
	"crypto/rand"
	"crypto/subtle"
	"encoding/base64"
	"errors"
	"fmt"
	"github.com/sirupsen/logrus"
	"golang.org/x/crypto/argon2"
	"strings"
)

type PasswordConfig struct {
	time    uint32
	memory  uint32
	threads uint8
	keyLen  uint32
}

func CreateDefaultPasswordConfig() *PasswordConfig {
	return &PasswordConfig{
		time:    1,
		memory:  64 * 1024,
		threads: 4,
		keyLen:  32,
	}
}

// GeneratePassword is used to generate a new password hash for storing and
// comparing at a later date.
func GeneratePassword(password string) (string, error) {
	// Generate a Salt
	salt := make([]byte, 16)
	if _, err := rand.Read(salt); err != nil {
		return "", err
	}

	c := CreateDefaultPasswordConfig()
	hash := argon2.IDKey([]byte(password), salt, c.time, c.memory, c.threads, c.keyLen)

	// Base64 encode the salt and hashed password.
	b64Salt := base64.RawStdEncoding.EncodeToString(salt)
	b64Hash := base64.RawStdEncoding.EncodeToString(hash)

	format := "$argon2id$v=%d$m=%d,t=%d,p=%d$%s$%s"
	full := fmt.Sprintf(format, argon2.Version, c.memory, c.time, c.threads, b64Salt, b64Hash)
	return base64.RawStdEncoding.EncodeToString([]byte(full)), nil
}

// ComparePassword is used to compare a user-inputted password to a hash to see
// if the password matches or not.
func ComparePassword(password string, hash string) (bool, error) {
	passwordByte, _ := base64.RawStdEncoding.DecodeString(password)
	password = string(passwordByte)

	parts := strings.Split(hash, "$")

	if len(parts) < 3 {
		logrus.Warning("Password format is invalid. To properly encode a password use `backup-repository " +
			"--encode-password='your-password'` and put it in `kind: Secret` or in `kind: BackupUser`")

		return false, errors.New("invalid password hash format. Check `kind: Secret` or `kind: BackupUser`")
	}

	c := &PasswordConfig{}
	_, err := fmt.Sscanf(parts[3], "m=%d,t=%d,p=%d", &c.memory, &c.time, &c.threads)
	if err != nil {
		return false, err
	}

	salt, err := base64.RawStdEncoding.DecodeString(parts[4])
	if err != nil {
		return false, err
	}

	decodedHash, err := base64.RawStdEncoding.DecodeString(parts[5])
	if err != nil {
		return false, err
	}
	c.keyLen = uint32(len(decodedHash))

	comparisonHash := argon2.IDKey([]byte(password), salt, c.time, c.memory, c.threads, c.keyLen)

	return subtle.ConstantTimeCompare(decodedHash, comparisonHash) == 1, nil
}
