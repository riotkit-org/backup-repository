package health

import (
	"github.com/pkg/errors"
	"gorm.io/gorm"
)

type DbValidator struct {
	db *gorm.DB
}

func (v DbValidator) Validate() error {
	var result int
	err := v.db.Raw("SELECT 161").Scan(&result).Error
	if err != nil || result != 161 {
		return errors.Wrapf(err, "cannot connect to database")
	}

	return nil
}

func NewDbValidator(db *gorm.DB) DbValidator {
	return DbValidator{db}
}
