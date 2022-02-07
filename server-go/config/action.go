package config

import (
	"errors"
	"fmt"
	log "github.com/sirupsen/logrus"
	"k8s.io/client-go/dynamic"
	"sigs.k8s.io/controller-runtime/pkg/client/config"
)

func CreateConfigurationProvider(providerName string) (ConfigurationProvider, error) {

	if providerName == "kubernetes" {
		log.Debugf("Initializing Kubernetes configuration provider, attempting to read KUBECONFIG")
		client, err := dynamic.NewForConfig(config.GetConfigOrDie())

		if err != nil {
			log.Errorf("Cannot initialize Kubernetes configuration provider: %v", err)
			return nil, err
		}

		return CreateKubernetesConfigurationProvider(
			client,
			"default",
		), nil
	}

	return nil, errors.New(fmt.Sprintf("Invalid configuration provider name '%v'", providerName))
}
