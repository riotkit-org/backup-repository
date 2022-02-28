package health

import (
	"github.com/pkg/errors"
	"github.com/riotkit-org/backup-repository/config"
)

type ConfigurationProviderValidator struct {
	cfg config.ConfigurationProvider
}

func (v ConfigurationProviderValidator) Validate() error {
	if err := v.cfg.GetHealth(); err != nil {
		return errors.Wrapf(err, "configuration provider is not usable")
	}

	return nil
}

func NewConfigurationProviderValidator(cfg config.ConfigurationProvider) ConfigurationProviderValidator {
	return ConfigurationProviderValidator{cfg}
}
