package config

import (
	k8sClient "sigs.k8s.io/controller-runtime/pkg/client"
	"sigs.k8s.io/controller-runtime/pkg/client/config"
)

func CreateConfigurationProvider(providerName string) (ConfigurationProvider, error) {
	client, err := k8sClient.New(config.GetConfigOrDie(), k8sClient.Options{})
	if err != nil {
		return nil, err
	}

	return CreateKubernetesConfigurationProvider(
		client,
		"default",
	), nil
}
