package health

import (
	"github.com/pkg/errors"
	"gorm.io/gorm"
)

type DbValidator struct {
	db *gorm.DB
}

func (v DbValidator) Validate() error {
	err := v.db.Raw("SELECT 1").Error
	if err != nil {
		return errors.Wrapf(err, "cannot connect to database")
	}
	return nil
}

func NewDbValidator(db *gorm.DB) DbValidator {
	return DbValidator{db}
}
