
from . import BackupDefinition
from ..access import ServerAccess
from ..encryption import Encryption


class CommandOutputDefinition(BackupDefinition):
    _command = ""
    _restore_command = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 command: str,
                 restore_command: str,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._command = command
        self._restore_command = restore_command

    def get_command(self) -> str:
        return self._command

    def get_restore_command(self) -> str:
        return self._restore_command

    @staticmethod
    def from_config(config: dict, name: str):
        return CommandOutputDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            command=config['command'],
            restore_command=config.get('restore_command', ''),
            name=name
        )


class LocalFileDefinition(BackupDefinition):
    _paths = ""

    def __init__(self,
                 access: ServerAccess,
                 _type: str,
                 collection_id: str,
                 encryption: Encryption,
                 tar_pack_cmd: str,
                 tar_unpack_cmd: str,
                 paths: list,
                 name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)
        self._paths = paths

    def get_paths(self) -> list:
        return self._paths

    @staticmethod
    def from_config(config: dict, name: str):
        return LocalFileDefinition(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            paths=config['paths'],
            name=name
        )
