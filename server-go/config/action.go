package config

import (
	"errors"
	"fmt"
)

func CreateConfigurationProvider(providerName string) (ConfigurationProvider, error) {

	if providerName == "kubernetes" {
		return CreateKubernetesConfigurationProvider(
			"default",
		), nil
	}

	return nil, errors.New(fmt.Sprintf("Invalid configuration provider name '%v'", providerName))
}
