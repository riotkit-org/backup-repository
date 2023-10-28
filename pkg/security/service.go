package security

import (
	"encoding/base64"
	"encoding/json"
	"errors"
	"fmt"
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/sirupsen/logrus"
	"github.com/tidwall/gjson"
	"gorm.io/gorm"
	"strings"
	"time"
)

type Service struct {
	repository GrantedAccessRepository
}

func (s Service) StoreJWTAsGrantedAccess(token string, expire time.Time, ip string, description string, username string, accessKeyName string) string {
	ga := NewGrantedAccess(token, expire, false, description, ip, username, accessKeyName)
	if err := s.repository.create(&ga); err != nil {
		logrus.Errorf("Cannot store GrantedAccess. Possibly tried to store same JWT twice. IsError: %v", err)
		return ""
	}
	return ga.ID
}

func (s Service) IsTokenStillValid(jwt string) bool {
	ga, err := s.repository.getGrantedAccessByHashedToken(HashJWT(jwt))
	if err != nil {
		logrus.Errorf("IsTokenValid(false): %v", err)
		return false
	}
	return ga.IsValid()
}

func (s Service) GetGrantedAccessInformation(jwt string) (GrantedAccess, error) {
	return s.repository.getGrantedAccessByHashedToken(HashJWT(jwt))
}

func (s Service) RevokeSessionBySessionId(sessionId string) error {
	if !s.repository.checkSessionExistsById(sessionId) {
		return errors.New("specified session identifier is not valid, cannot find GrantedAccess of specified id")
	}

	return s.repository.revokeById(sessionId)
}

func (s Service) RevokeSessionByJWT(jwt string) error {
	return s.RevokeSessionBySessionId(HashJWT(jwt))
}

func (s Service) GetAllGrantedAccessesForUserByUsername(name string) []GrantedAccess {
	return s.repository.findForUsername(name)
}

func (s Service) GetGrantedAccessInformationBySessionId(sessionId string) (GrantedAccess, error) {
	return s.repository.findOneBySessionId(sessionId)
}

func NewService(db *gorm.DB) Service {
	return Service{
		repository: GrantedAccessRepository{
			db: db,
		},
	}
}

func extractJsonFromJWT(jwt string) (string, error) {
	// optionally extract token from Authorization header
	if strings.HasPrefix(jwt, "Bearer") {
		jwt = jwt[7:]
	}
	split := strings.SplitN(jwt, ".", 3)
	json, err := base64.RawStdEncoding.DecodeString(split[1])
	if err != nil {
		return "", errors.New(fmt.Sprintf("cannot extract JSON from JWT: %s", err.Error()))
	}
	return string(json), nil
}

// ExtractLoginFromJWT returns username of a user that owns this token
func ExtractLoginFromJWT(jwt string) (string, string) {
	json, err := extractJsonFromJWT(jwt)
	if err != nil {
		logrus.Warnf("invalid JWT format: %s", err.Error())
	}

	username := gjson.Get(json, "login")
	accessKeyName := gjson.Get(json, "accessKeyName")

	return username.String(), accessKeyName.String()
}

func ExtractSessionLimitedOperationsScopeFromJWT(jwt string) (*SessionLimitedOperationsScope, error) {
	asJson, err := extractJsonFromJWT(jwt)
	if err != nil {
		logrus.Warnf("invalid JWT format: %s", err.Error())
	}

	scope := &SessionLimitedOperationsScope{}
	scopeJson := gjson.Get(asJson, ScopeClaimIndex)

	if scopeJson.Exists() {
		scopeAsTxtJson, decodeErr := base64.StdEncoding.DecodeString(scopeJson.String())
		if decodeErr != nil {
			return nil, errors.New(fmt.Sprintf("cannot base64 decode operations scope from JWT: %s", decodeErr.Error()))
		}

		scopeErr := json.Unmarshal([]byte(scopeAsTxtJson), &scope)
		if scopeErr != nil {
			return nil, errors.New(fmt.Sprintf("cannot unpack operations scope from JWT: %s", scopeErr.Error()))
		}
	}
	return scope, nil
}

// FillPasswordFromKindSecret is able to fill up object from a data retrieved from `kind: Secret` in Kubernetes
func FillPasswordFromKindSecret(r config.ConfigurationProvider, ref *PasswordFromSecretRef, setterCallback func(secret string)) error {
	if ref.Name != "" {
		// todo: cache support
		secretDoc, secretErr := r.GetSingleDocumentAnyType("secrets", ref.Name, "", "v1")
		if secretErr != nil {
			logrus.Errorf("Cannot fetch user hashed password from `kind: Secret`. Maybe it does not exist? %v", secretErr)
			return secretErr
		}

		secret := gjson.Get(secretDoc, fmt.Sprintf("data.%v", ref.Entry))
		if secret.String() == "" {
			logrus.Errorf(
				"Cannot retrieve password from `kind: Secret` of name '%v', field '%v'",
				ref.Name,
				ref.Entry,
			)
			return errors.New("invalid field name in `kind: Secret`")
		}

		setterCallback(secret.String())
		return nil
	}

	logrus.Warn("`kind: Secret` not used for entity")
	return nil
}
