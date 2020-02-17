
from . import ContainerizedDefinition


class RedisDefinition(ContainerizedDefinition):
    """
        Support for Redis KV store export
    """

    data_dir: str              # if not chosen, then will be auto-detected using redis-cli
    is_aof: bool               # if not chosen, will be auto-detected
    fix_aof_at_restore: bool
    restart_command: str       # if not chosen, then will be fallen back to systemd or docker restart

    @staticmethod
    def from_config(config: dict, name: str):
        definition = RedisDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', ContainerizedDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', ContainerizedDefinition._tar_unpack_cmd),
            name=name
        )

        definition._container = config.get('container', '')
        definition._docker_bin = config.get('docker_bin', 'docker')
        definition.data_dir = config.get('data_dir', None)
        definition.is_aof = config.get('is_aof', None)
        definition.fix_aof_at_restore = config.get('fix_aof_at_restore', False)
        definition.restart_command = config.get('restart_command', None)

        return definition

