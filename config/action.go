package config

import (
	"errors"
	"fmt"
)

func CreateConfigurationProvider(providerName string, namespace string) (ConfigurationProvider, error) {

	if providerName == "kubernetes" {
		return CreateKubernetesConfigurationProvider(
			namespace,
		), nil
	}

	return nil, errors.New(fmt.Sprintf("Invalid configuration provider name '%v'", providerName))
}
