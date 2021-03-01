
from rkd.api.syntax import TaskAliasDeclaration as Task
from rkd_python import imports as PythonBuildTasksImports


IMPORTS = PythonBuildTasksImports()

TASKS = [
    Task(':release:docker-image', [':sh', '-c', ''' set -x
        cd ../ && docker build . -f bahub/.rkd/docker/Dockerfile -t quay.io/riotkit/bahub:latest
    ''']),

    Task(':run:docker', [':sh', '-c', ''' set -x
        docker run --rm --name bahub -e CONFIG=bahub.conf.yaml quay.io/riotkit/bahub:latest
    ''']),

    Task(':env:adapters', [':sh', '-c', '''
        cd test/env/bahub_adapter_integrations && docker-compose -p bahub_adapter_integrations up -d
    ''']),

    Task(':env:bahub-docker:up', [':sh', '-c', '''
        docker-compose -p s3pb -f .rkd/ci/docker-compose.yml up -d
    '''])
]
