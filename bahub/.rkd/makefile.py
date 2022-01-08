import re
from rkd.api.contract import ExecutionContext, TaskInterface, ArgparseArgument
from rkd.api.syntax import TaskAliasDeclaration as Pipeline, TaskDeclaration as Task
from rkd.standardlib.core import CallableTask
from rkd_python import imports as PythonBuildTasksImports


def release_docker(ctx: ExecutionContext, task: TaskInterface) -> bool:
    tag = str(ctx.get_arg('ref')).replace('refs/tags/', '')
    should_push = ctx.get_arg('--push')

    if should_push:
        task.sh('docker push quay.io/riotkit/bahub:latest-dev')

    if re.match('^v([0-9.\-A-Z]+)$', tag):
        task.sh('docker tag quay.io/riotkit/bahub:latest-dev quay.io/riotkit/bahub:%s' % tag)
        task.rkd(
            ['@', '--image', 'quay.io/riotkit/bahub:%s' % tag, '--propagate', ':docker:tag']
            + ([':docker:push'] if should_push else [])
        )

        task.sh('docker tag quay.io/riotkit/bahub:latest-dev quay.io/riotkit/bahub:latest-release')
        task.sh('docker push quay.io/riotkit/bahub:latest-release')

        return True

    return True


IMPORTS = PythonBuildTasksImports() + [
    Task(CallableTask(':release:docker', callback=release_docker, argparse_options=[
        ArgparseArgument(['ref'], {'help': 'Tag name, or latest'}),
        ArgparseArgument(['--push'], {'help': 'Should push?', 'action': 'store_true'})
    ]))
]

TASKS = [
    Pipeline(':build:docker', [':sh', '-c', ''' set -x
        cd ../ && sudo docker build . -f bahub/.rkd/docker/Dockerfile -t quay.io/riotkit/bahub:latest-dev
    ''']),

    Pipeline(':test:dev:create', [':sh', '-c', '''
        docker run --name nginx_bahub_test -d nginx:1.19 || true;
    
        export TEST_COLLECTION_ID=$(cat ../backup-maker/.build/test/collection-id.txt);
        export SERVER_URL=$(cat ../backup-maker/.build/test/domain.txt);
        export API_TOKEN=$(cat ../backup-maker/.build/test/collection-id.txt);
        RKD_SYS_LOG_LEVEL=debug python -m bahub :backup:make -c ./bahub.conf.yaml fs_docker -rl debug
    ''']),

    Pipeline(':test:unit', [':py:unittest'])
]
