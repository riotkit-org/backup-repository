package health

import (
	"fmt"
	"github.com/pkg/errors"
	"gorm.io/gorm"
	"math/rand"
	"time"
)

type DbValidator struct {
	db *gorm.DB
}

func (v DbValidator) Validate() error {
	rand.Seed(time.Now().UnixNano())
	key := rand.Intn(8)
	var result int

	err := v.db.Raw(fmt.Sprintf("SELECT %v", key)).Scan(&result).Error
	if err != nil || result != key {
		return errors.Wrapf(err, "cannot connect to database")
	}

	return nil
}

func NewDbValidator(db *gorm.DB) DbValidator {
	return DbValidator{db}
}
