
k8s_postgres:
	helm repo add bitnami https://charts.bitnami.com/bitnami
	helm upgrade --install postgres bitnami/postgresql -n backup-repository --create-namespace --values ./helm/examples/postgresql.values.yaml --wait --timeout 2m0s

k8s_minio:
	helm repo add minio https://helm.min.io/
	helm upgrade --install minio minio/minio -n backup-repository --values ./helm/examples/minio.values.yaml --wait --timeout 2m0s

k8s_test_backup_repository:
	helm upgrade --install backup-repository ./helm/backup-repository-server -n backup-repository --values ./helm/examples/backup-repository-ci.values.yaml
