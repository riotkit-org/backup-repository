package config

import (
	"context"
	"k8s.io/apimachinery/pkg/apis/meta/v1/unstructured"
	"k8s.io/apimachinery/pkg/runtime/schema"
	k8sClient "sigs.k8s.io/controller-runtime/pkg/client"
)

type ConfigurationInKubernetes struct {
	api        k8sClient.Client
	namespace  string
	apiGroup   string
	apiVersion string
}

func (o ConfigurationInKubernetes) GetSingleDocument(kind string, id string) (string, error) {
	object := &unstructured.Unstructured{}
	object.SetGroupVersionKind(schema.GroupVersionKind{
		Group:   o.apiGroup,
		Kind:    kind,
		Version: o.apiVersion,
	})

	if err := o.api.Get(context.Background(), k8sClient.ObjectKey{Namespace: o.namespace, Name: id}, object); err != nil {
		return "", err
	}

	content, err := object.MarshalJSON()
	if err != nil {
		return "", err
	}

	return string(content), nil
}

func CreateKubernetesConfigurationProvider(api k8sClient.Client, namespace string) ConfigurationInKubernetes {
	return ConfigurationInKubernetes{
		api:        api,
		namespace:  namespace,
		apiVersion: "v1alpha1",
		apiGroup:   "backups.riotkit.org",
	}
}
