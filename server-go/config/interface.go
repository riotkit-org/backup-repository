package config

type ConfigurationProvider interface {
	GetSingleDocument(kind string, id string) (string, error)
	GetSingleDocumentAnyType(kind string, id string, apiGroup string, apiVersion string) (string, error)

	// SaveDocument(document interface{})
}
