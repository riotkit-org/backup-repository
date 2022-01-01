package client

func GetShellCommand(cmd string) []string {
	return []string{"-eo", "pipefail", "-c", cmd}
}
