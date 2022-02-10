package security

import (
	"encoding/base64"
	"github.com/sirupsen/logrus"
	"github.com/tidwall/gjson"
	"gorm.io/gorm"
	"strings"
	"time"
)

type Service struct {
	repository GrantedAccessRepository
}

func (s Service) StoreJWTAsGrantedAccess(token string, expire time.Time, ip string, description string, username string) string {
	ga := NewGrantedAccess(token, expire, false, description, ip, username)
	s.repository.create(&ga)

	return ga.ID
}

func (s Service) IsTokenStillValid(token string) bool {
	if _, err := s.repository.getGrantedAccessByHashedToken(HashJWT(token)); err != nil {
		logrus.Debugf("IsTokenValid(false): %v", err)
		return false
	}

	return true
}

func NewService(db *gorm.DB) Service {
	return Service{
		repository: GrantedAccessRepository{
			db: db,
		},
	}
}

// ExtractLoginFromJWT returns username of a user that owns this token
func ExtractLoginFromJWT(jwt string) string {
	split := strings.SplitN(jwt, ".", 3)

	json, err := base64.RawStdEncoding.DecodeString(split[1])
	if err != nil {
		logrus.Errorf("Cannot extract login from JWT, %v", err)
		return ""
	}

	username := gjson.Get(string(json), "login")
	return username.String()
}
