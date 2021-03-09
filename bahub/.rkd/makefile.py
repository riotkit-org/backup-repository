
from rkd.api.syntax import TaskAliasDeclaration as Task
from rkd_python import imports as PythonBuildTasksImports


IMPORTS = PythonBuildTasksImports()

TASKS = [
    Task(':build:docker', [':sh', '-c', ''' set -x
        cd ../ && sudo docker build . -f bahub/.rkd/docker/Dockerfile -t quay.io/riotkit/bahub:latest-dev
    ''']),

    Task(':release:docker', [':sh', '-c', ''' set -x
        cd ../ && docker push quay.io/riotkit/bahub:latest-dev
    ''']),

    Task(':test:unit', [':py:unittest'])
]
