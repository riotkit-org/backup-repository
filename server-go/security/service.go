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
	ga, err := s.repository.getGrantedAccessByHashedToken(HashJWT(token))

	if err != nil {
		logrus.Errorf("IsTokenValid(false): %v", err)
		return false
	}

	return ga.IsValid()
}

func (s Service) GetGrantedAccessInformation(token string) (GrantedAccess, error) {
	return s.repository.getGrantedAccessByHashedToken(HashJWT(token))
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
	// optionally extract token from Authorization header
	if strings.HasPrefix(jwt, "Bearer") {
		jwt = jwt[7:]
	}

	split := strings.SplitN(jwt, ".", 3)

	json, err := base64.RawStdEncoding.DecodeString(split[1])
	if err != nil {
		logrus.Errorf("Cannot extract login from JWT, %v", err)
		return ""
	}

	username := gjson.Get(string(json), "login")
	return username.String()
}
