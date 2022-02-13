package collections

import (
	"github.com/riotkit-org/backup-repository/config"
	"github.com/riotkit-org/backup-repository/security"
)

type StrategySpec struct {
	KeepLastOlderNotMoreThan string `json:"keepLastOlderNotMoreThan"`
	MaxOlderCopies           int    `json:"maxOlderCopies"`
}

type BackupWindow struct {
	From     string `json:"from"`
	Duration string `json:"duration"`
}

type BackupWindows []BackupWindow

type Spec struct {
	Description       string                     `json:"description"`
	FilenameTemplate  string                     `json:"filenameTemplate"`
	MaxBackupsCount   int                        `json:"maxBackupsCount"`
	MaxOneVersionSize string                     `json:"maxOneVersionSize"`
	MaxCollectionSize string                     `json:"maxCollectionSize"`
	Windows           BackupWindows              `json:"windows"`
	StrategyName      string                     `json:"strategyName"`
	StrategySpec      StrategySpec               `json:"strategySpec"`
	AccessControl     security.AccessControlList `json:"accessControl"`
}

type Collection struct {
	Metadata config.ObjectMetadata `json:"metadata"`
}
