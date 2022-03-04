
k8s_postgres:
	helm repo add bitnami https://charts.bitnami.com/bitnami
	helm upgrade --install postgres bitnami/postgresql -n backup-repository --create-namespace --values ./helm/examples/postgresql.values.yaml --wait --timeout 2m0s

k8s_minio:
	helm repo add minio https://helm.min.io/
	helm upgrade --install minio minio/minio -n backup-repository --values ./helm/examples/minio.values.yaml --wait --timeout 2m0s

k8s_test_backup_repository:
	helm upgrade --install backup-repository ./helm/backup-repository-server -n backup-repository --values ./helm/examples/backup-repository-ci.values.yaml --wait --timeout 30s

k8s_crd:
	kubectl apply -f crd

k8s_install: k8s_postgres k8s_minio k8s_crd k8s_test_backup_repository

k8s_test:
	kubectl get svc -A
	kubectl get pods -A
	kubectl get events -A
	kubectl logs deployment/backup-repository-backup-repository-server -n backup-repository

	curl -vvv -k http://localhost:30080/health

	if kubectl get events -A | grep "backup-repository" | grep "Failed" 2>&1 > /dev/null; then \
	    exit 1; \
	fi;
