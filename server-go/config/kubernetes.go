package config

import (
	"context"
	"github.com/sirupsen/logrus"
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

func (o ConfigurationInKubernetes) GetSingleDocumentAnyType(kind string, id string, apiGroup string, apiVersion string) (string, error) {
	object := &unstructured.Unstructured{}
	object.SetGroupVersionKind(schema.GroupVersionKind{
		Group:   apiGroup,
		Kind:    kind,
		Version: apiVersion,
	})

	if err := o.api.Get(context.Background(), k8sClient.ObjectKey{Namespace: o.namespace, Name: id}, object); err != nil {
		logrus.Warnf("Kubernetes API returned error: %v", err)
		return "", err
	}

	content, err := object.MarshalJSON()
	if err != nil {
		logrus.Errorf("Cannot return Kubernetes object of kind '%v' as JSON", kind)
		return "", err
	}

	logrus.Debugf("GetSingleDocument(%v, %v) OK", kind, id)
	return string(content), nil
}

func (o ConfigurationInKubernetes) GetSingleDocument(kind string, id string) (string, error) {
	return o.GetSingleDocumentAnyType(kind, id, o.apiGroup, o.apiVersion)
}

func CreateKubernetesConfigurationProvider(api k8sClient.Client, namespace string) ConfigurationInKubernetes {
	// todo: Implement caching by composition
	return ConfigurationInKubernetes{
		api:        api,
		namespace:  namespace,
		apiVersion: "v1alpha1",
		apiGroup:   "backups.riotkit.org",
	}
}
