package config_test

import (
	"github.com/riotkit-org/backup-repository/config"
	"github.com/stretchr/testify/assert"
	"os"
	"reflect"
	"testing"
)

// basically checks if filesystem provider can be set up
func TestCreateConfigurationProvider_Filesystem(t *testing.T) {
	wd, _ := os.Getwd()
	provider, err := config.CreateConfigurationProvider("filesystem", "backup-repository", wd+"/../docs/examples-filesystem")

	assert.Nil(t, err)
	assert.Equal(t, "*config.ConfigurationInLocalFilesystem", reflect.TypeOf(provider).String())
}

// checks if error is returned, when provider is unknown
func TestCreateConfigurationProvider_UnknownProvider(t *testing.T) {
	_, err := config.CreateConfigurationProvider("this-is-not-valid", "backup-repository", "")
	assert.NotNil(t, err)
}
