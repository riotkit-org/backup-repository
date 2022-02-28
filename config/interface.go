package config

type ConfigurationProvider interface {
	GetSingleDocument(kind string, id string) (string, error)
	GetSingleDocumentAnyType(kind string, id string, apiGroup string, apiVersion string) (string, error)

	StoreDocument(kind string, document interface{}) error
}
