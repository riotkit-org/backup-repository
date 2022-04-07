IMAGE_REPOSITORY=ghcr.io/riotkit-org/backup-repository
IMAGE_TAG=snapshot
DEV_LOCAL_IMAGE_REPOSITORY=127.0.0.1:30050/backup-repository

k3d: ## Run local empty Kubernetes cluster
	k3d cluster create riotkit --agents 1 -p "30080:30080@agent:0" -p "30081:30081@agent:0" -p "30050:30050@agent:0"

k8s_postgres:
	helm repo add bitnami https://charts.bitnami.com/bitnami
	helm upgrade --install postgres bitnami/postgresql -n backup-repository --create-namespace --values ./helm/examples/postgresql.values.yaml --wait --timeout 2m0s

k8s_minio:
	helm repo add minio https://helm.min.io/
	helm upgrade --install minio minio/minio -n backup-repository --values ./helm/examples/minio.values.yaml --wait --timeout 2m0s

k8s_test_backup_repository:
	helm upgrade --install backup-repository ./helm/backup-repository-server -n backup-repository --values ./helm/examples/backup-repository-ci.values.yaml --wait --timeout 30s --set image.repository=${IMAGE_REPOSITORY} --set image.tag=${IMAGE_TAG} || (kubectl get events -A; exit 1)

k8s_crd:
	kubectl apply -f helm/backup-repository-server/templates/crd.yaml

k8s_dev_registry:
	helm repo add twuni https://helm.twun.io
	helm upgrade --install registry twuni/docker-registry -n default --set ingress.enabled=true --set ingress.hosts[0]=registry.ingress.cluster.local
	kubectl apply -f tests/.helpers/local-registry.yaml

k8s_install: k8s_postgres k8s_minio k8s_test_backup_repository

k8s_test:
	sleep 10

	kubectl get svc -A
	kubectl get pods -A
	kubectl get events -A
	kubectl logs deployment/backup-repository-backup-repository-server -n backup-repository

	curl -vvv -k http://localhost:30081/ready?code=changeme --fail-early

#################################
## Local development environment
#################################

k8s_publish_dev_registry: ## Publish to local Kubernetes registry
	docker build . -t ${DEV_LOCAL_IMAGE_REPOSITORY}:snapshot
	docker push ${DEV_LOCAL_IMAGE_REPOSITORY}:snapshot

k8s_test_promote: ## Build & Push & Install at local Kubernetes
	make build k8s_crd k8s_publish_dev_registry k8s_test_backup_repository IMAGE_REPOSITORY=${DEV_LOCAL_IMAGE_REPOSITORY}
