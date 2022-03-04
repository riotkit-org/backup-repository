
k8s_postgres:
	helm repo add bitnami https://charts.bitnami.com/bitnami
	helm upgrade --install postgres bitnami/postgresql -n backup-repository --create-namespace --set-file ./helm/examples/postgresql.values.yaml

k8s_minio:
	helm repo add minio https://helm.min.io/
	helm upgrade --install minio minio/minio -n backup-repository --set-file ./helm/examples/minio.values.yaml

k8s_test_backup_repository:
	helm install backup-repository ./helm/backup-repository-server -n backup-repository --set-file ./helm/examples/backup-repository-ci.values.yaml
