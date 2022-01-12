import os

HOME_PATH = os.path.expanduser("~/.backup-controller")
BIN_CACHE_PATH = HOME_PATH + "/bin"
BIN_VERSION_CACHE_PATH = BIN_CACHE_PATH + '/versions'
CONFIG_PATH = os.path.expanduser("~/.backup-controller/config.yaml")

TARGET_ENV_BIN_PATH = "/tmp/.br"
TARGET_ENV_VERSIONS_PATH = "/tmp/.br/versions"
