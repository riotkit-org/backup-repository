package config

import (
	"context"
	"github.com/fatih/structs"
	"github.com/sirupsen/logrus"
	metav1 "k8s.io/apimachinery/pkg/apis/meta/v1"
	"k8s.io/apimachinery/pkg/apis/meta/v1/unstructured"
	"k8s.io/apimachinery/pkg/runtime/schema"
	"k8s.io/client-go/dynamic"
)

type ConfigurationInKubernetes struct {
	api        dynamic.Interface
	namespace  string
	apiGroup   string
	apiVersion string
}

func (o ConfigurationInKubernetes) GetSingleDocumentAnyType(kind string, id string, apiGroup string, apiVersion string) (string, error) {
	resource := schema.GroupVersionResource{Group: apiGroup, Version: apiVersion, Resource: kind}
	object, err := o.api.Resource(resource).Namespace(o.namespace).Get(context.Background(), id, metav1.GetOptions{})

	if err != nil {
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

func (o ConfigurationInKubernetes) StoreDocument(kind string, document interface{}) error {
	resource := schema.GroupVersionResource{Group: o.apiGroup, Version: o.apiVersion, Resource: kind}
	object := unstructured.Unstructured{Object: structs.Map(document)}

	_, err := o.api.Resource(resource).Namespace(o.namespace).Create(
		context.Background(),
		&object,
		metav1.CreateOptions{},
	)

	// todo: if update fails specifically, then attempt to create object

	if err != nil {
		logrus.Errorf("Cannot stored document of `kind: %v`. Error: %v", kind, err)
		return err
	}

	return nil
}

func CreateKubernetesConfigurationProvider(api dynamic.Interface, namespace string) ConfigurationInKubernetes {
	// todo: Implement caching by composition
	return ConfigurationInKubernetes{
		api:        api,
		namespace:  namespace,
		apiVersion: "v1alpha1",
		apiGroup:   "backups.riotkit.org",
	}
}
