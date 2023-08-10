package config

import (
	"encoding/json"
	"fmt"
	"github.com/pkg/errors"
	"github.com/sirupsen/logrus"
	"io/ioutil"
	"k8s.io/apimachinery/pkg/util/yaml"
	"os"
	"strings"
	"time"
)

type ConfigurationInLocalFilesystem struct {
	path       string
	namespace  string
	apiGroup   string
	apiVersion string
}

func (fs *ConfigurationInLocalFilesystem) GetHealth() error {
	fileName := fmt.Sprintf("%s/.health-%v", fs.path, time.Now().UnixNano())
	defer func() {
		_ = os.Remove(fileName)
	}()

	if err := ioutil.WriteFile(fileName, []byte(fileName), 0700); err != nil {
		return errors.Wrap(err, "The filesystem is not writeable")
	}

	content, err := ioutil.ReadFile(fileName)
	if err != nil {
		return errors.Wrap(err, "Cannot read file that was written to the filesystem")
	}
	if string(content) != fileName {
		return errors.Wrap(err, "Filesystem consistency error, read data does not match wrote data")
	}

	return nil
}

func (fs *ConfigurationInLocalFilesystem) GetSingleDocumentAnyType(kind string, id string, apiGroup string, apiVersion string) (string, error) {
	filePath := fs.buildPath(kind, id, apiGroup, apiVersion)
	logrus.Debugf("Looking for file at path '%s'", filePath)

	if _, err := os.Stat(filePath); errors.Is(err, os.ErrNotExist) {
		return "", errors.Wrap(err, "Object not found")
	}

	content, err := ioutil.ReadFile(filePath)
	if err != nil {
		return "", errors.Wrapf(err, "Cannot read object from filesystem storage at path '%s'", filePath)
	}
	recode, err := fs.recodeFromYamlToJson(content)
	if err != nil {
		return "", errors.Wrapf(err, "Cannot parse object from filesystem storage at path '%s'", filePath)
	}

	return recode, nil
}

func (fs *ConfigurationInLocalFilesystem) GetSingleDocument(kind string, id string) (string, error) {
	return fs.GetSingleDocumentAnyType(kind, id, fs.apiGroup, fs.apiVersion)
}

func (fs *ConfigurationInLocalFilesystem) StoreDocument(kind string, document interface{}) error {
	return errors.New("not implemented")
}

func (fs *ConfigurationInLocalFilesystem) buildPath(kind string, id string, apiGroup string, apiVersion string) string {
	return strings.ReplaceAll(fs.path+"/"+fs.namespace+"/"+apiGroup+"/"+apiVersion+"/"+kind+"/"+id+".yaml", "//", "/")
}

func (fs *ConfigurationInLocalFilesystem) recodeFromYamlToJson(yamlDoc []byte) (string, error) {
	var raw interface{}
	if err := yaml.Unmarshal(yamlDoc, &raw); err != nil {
		return "", errors.Wrap(err, "Cannot recode from YAML to JSON")
	}
	jsonDoc, err := json.Marshal(raw)
	if err != nil {
		return "", errors.Wrap(err, "Cannot recode from YAML to JSON")
	}
	return string(jsonDoc), nil
}

func NewConfigurationInLocalFilesystemProvider(path string, namespace string) *ConfigurationInLocalFilesystem {
	return &ConfigurationInLocalFilesystem{
		path:       path,
		namespace:  namespace,
		apiVersion: "v1alpha1",
		apiGroup:   "backups.riotkit.org",
	}
}
