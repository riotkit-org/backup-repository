package config

import (
	"errors"
	"fmt"
	k8sClient "sigs.k8s.io/controller-runtime/pkg/client"
	"sigs.k8s.io/controller-runtime/pkg/client/config"
)

func CreateConfigurationProvider(providerName string) (ConfigurationProvider, error) {

	if providerName == "kubernetes" {
		client, err := k8sClient.New(config.GetConfigOrDie(), k8sClient.Options{})
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
