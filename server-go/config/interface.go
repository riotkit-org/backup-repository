package config

type ConfigurationProvider interface {
	GetSingleDocument(kind string, id string) (string, error)

	// SaveDocument(document interface{})
}
