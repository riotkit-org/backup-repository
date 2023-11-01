package config_test

import (
	"github.com/riotkit-org/backup-repository/pkg/config"
	"github.com/stretchr/testify/assert"
	"os"
	"testing"
)

func TestConfigurationInLocalFilesystem_GetHealth_FailsOnNonWritableDirectory(t *testing.T) {
	// we hope nobody runs those tests as sudo/root ;)
	nonWritableFailing := config.NewConfigurationInLocalFilesystemProvider("/usr/share/putin-chuj", "default")
	err := nonWritableFailing.GetHealth()

	assert.NotNil(t, err)
	assert.Contains(t, err.Error(), "The filesystem is not writeable")
}

func TestConfigurationInLocalFilesystem_GetHealth(t *testing.T) {
	// we hope nobody runs those tests as sudo/root ;)
	wd, _ := os.Getwd()
	valid := config.NewConfigurationInLocalFilesystemProvider(wd, "default")
	err := valid.GetHealth()
	assert.Nil(t, err)
}

func TestConfigurationInLocalFilesystem_GetSingleDocument_WithCRD(t *testing.T) {
	wd, _ := os.Getwd()
	provider := config.NewConfigurationInLocalFilesystemProvider(wd+"/../../docs/examples-filesystem", "backup-repository")

	admin, err := provider.GetSingleDocument("backupusers", "admin")

	assert.Nil(t, err)
	assert.Contains(t, admin, `"kind":"BackupUser"`)
}

func TestConfigurationInLocalFilesystem_GetSingleDocument_WithStandardResource(t *testing.T) {
	wd, _ := os.Getwd()
	provider := config.NewConfigurationInLocalFilesystemProvider(wd+"/../../docs/examples-filesystem", "backup-repository")

	admin, err := provider.GetSingleDocumentAnyType("secrets", "backup-repository-passwords", "", "v1")

	assert.Nil(t, err)
	assert.Contains(t, admin, `"kind":"Secret"`)
}

func TestConfigurationInLocalFilesystem_GetSingleDocument_NotFound(t *testing.T) {
	wd, _ := os.Getwd()
	provider := config.NewConfigurationInLocalFilesystemProvider(wd+"/../../docs/examples-filesystem", "backup-repository")

	_, err := provider.GetSingleDocument("backupusers", "some-non-existing-object")

	assert.NotNil(t, err)
	assert.Contains(t, err.Error(), "Object not found")
}
