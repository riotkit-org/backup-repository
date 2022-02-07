package config

import (
	"errors"
	"fmt"
	"k8s.io/client-go/dynamic"
	"sigs.k8s.io/controller-runtime/pkg/client/config"
)

func CreateConfigurationProvider(providerName string) (ConfigurationProvider, error) {

	if providerName == "kubernetes" {
		client, err := dynamic.NewForConfig(config.GetConfigOrDie())

		if err != nil {
			return nil, err
		}

		return CreateKubernetesConfigurationProvider(
			client,
			"default",
		), nil
	}

	return nil, errors.New(fmt.Sprintf("Invalid configuration provider name '%v'", providerName))
}
