
from rkd.api.syntax import TaskAliasDeclaration as Task
from rkd_python import imports as PythonBuildTasksImports


IMPORTS = PythonBuildTasksImports()

TASKS = [
    Task(':build:docker', [':sh', '-c', ''' set -x
        cd ../ && docker build . -f bahub/.rkd/docker/Dockerfile -t quay.io/riotkit/bahub:latest
    ''']),

    Task(':run:docker', [':sh', '-c', ''' set -x
        docker run --rm --name bahub -e CONFIG=bahub.conf.yaml quay.io/riotkit/bahub:latest
    '''])
]
