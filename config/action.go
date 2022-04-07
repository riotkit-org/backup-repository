package config

import (
	"errors"
	"fmt"
)

// CreateConfigurationProvider is a configuration factory
func CreateConfigurationProvider(providerName string, namespace string, localPath string) (ConfigurationProvider, error) {
	if providerName == "kubernetes" {
		return CreateKubernetesConfigurationProvider(
			namespace,
		), nil
	} else if providerName == "filesystem" {
		return NewConfigurationInLocalFilesystemProvider(
			localPath,
			namespace,
		), nil
	}

	return nil, errors.New(fmt.Sprintf("Invalid configuration provider name '%v'", providerName))
}
