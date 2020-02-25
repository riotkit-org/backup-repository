
from . import ContainerizedDefinition
from . import BackupDefinition
from ..access import ServerAccess
from ..encryption import Encryption
from abc import ABC, abstractmethod


class AbstractSQLDefinition(ABC, ContainerizedDefinition):
    """ Common model for all SQL backups, contains connection details that are common across all databases """

    _host: str
    _port: int
    _user: str
    _password: str
    _database: str

    def __init__(self,
                 access: ServerAccess, _type: str, collection_id: str, encryption: Encryption,
                 tar_pack_cmd: str, tar_unpack_cmd: str, host: str, port: int, user: str,
                 password: str, database: str, docker_bin: str, container: str, name: str):

        super().__init__(access, _type, collection_id, encryption, tar_pack_cmd, tar_unpack_cmd, name)

        self._host = host
        self._port = port
        self._user = user
        self._password = password
        self._database = database
        self._docker_bin = docker_bin
        self._container = container

    def get_database(self) -> str:
        return self._database

    def fill_template(self, template: str):
        return template\
            .replace('%host%', self._host) \
            .replace('%user%', self._user) \
            .replace('%port%', str(self._port)) \
            .replace('%database%', self.get_database()) \
            .replace('%password%', self._password)

    def get_sensitive_information(self):
        return [self._password]


class AbstractDumpAndRestoreDefinition(AbstractSQLDefinition):
    """ Common model for dump & restore workflow when working with databases """

    _dump_cmd: str
    _restore_cmd: str
    _query_cmd: str

    _dump_opts: str
    _restore_opts: str

    def fill_template(self, template: str):
        return super().fill_template(template) \
            .replace('%dump_opts%', self._dump_opts) \
            .replace('%restore_opts%', self._restore_opts)

    @classmethod
    def from_config(cls, config: dict, name: str):
        definition = cls(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            host=config['host'],
            port=int(config['port']),
            user=config['user'],
            password=config['password'],
            database=config['database'],
            docker_bin=config.get('docker_bin', 'docker'),
            container=config.get('container', ''),
            name=name
        )

        definition._dump_opts = config.get('dump_opts', '')
        definition._restore_opts = config.get('restore_opts', '')

        definition._init_cmds(config)
        return definition

    def get_dump_command(self) -> str:
        return self.fill_template(self._dump_cmd)

    def get_restore_command(self):
        return self.fill_template(self._restore_cmd)

    @abstractmethod
    def _init_cmds(self, config: dict):
        raise NotImplementedError('Please implement _init_cmds() in child class')

    def __repr__(self):
        return 'Definition<name=' + self._name + ',collection_id=' + \
               str(self.get_collection_id()) + ',docker_container=' + \
               str(self.get_container()) + ',sql_host=' + str(self._host) + '>'


class MySQLDefinition(AbstractDumpAndRestoreDefinition):
    """ MySQL backup model using mysqldump command """

    def _init_cmds(self, config: dict):
        self._dump_cmd = config.get('dump_cmd')
        self._restore_cmd = config.get('restore_cmd')

        if not self._dump_opts:
            self._dump_opts = ' --skip-lock-tables --add-drop-table --add-drop-database --add-drop-trigger '

        if not self._dump_cmd:
            self._dump_cmd = \
                'mysqldump %dump_opts% -u %user% -P %port% -p%password% ' + \
                '-h %host% --all-databases'

            if self.get_database():
                self._dump_cmd = \
                    'mysqldump %dump_opts% -u %user% -P %port% -p%password% ' + \
                    '-h %host% %database% '

        if not self._restore_cmd:
            self._restore_cmd = 'mysql -u %user% -p%password% -h %host% -P %port%'

            if self.get_database():
                self._restore_cmd = 'mysql -u %user% -p%password% -h %host% -P %port% %database%'

    def get_database(self) -> str:
        return str(self._database)

    def is_copying_all_databases(self) -> bool:
        return self.get_database() != ''


class PostgreSQLDefinition(AbstractDumpAndRestoreDefinition):
    """ PostgreSQL definition for using pg_dump pg_dumpall and pg_restore """

    def _init_cmds(self, config: dict):
        self._dump_cmd = config.get('dump_cmd')

        if not self._dump_cmd:
            #
            # Decide if we use pg_dump or pg_dumpall
            #
            if self.get_database():
                pg_dump_part = 'pg_dump -U %user% -p %port% -h %host% -d %database% -c %dump_opts%'
            else:
                pg_dump_part = 'pg_dumpall -U %user% -p %port% -h %host% -c %dump_opts%'

            self._dump_cmd = 'PGPASSWORD=%password% ' + pg_dump_part

        self._restore_cmd = config.get('restore_cmd')

        if not self._restore_cmd:
            db = self.get_database() if self.get_database() else 'postgres'
            self._restore_cmd = self.create_psql_cmdline(db)

    def terminate_all_sessions_command(self) -> str:
        return self.fill_template(
            'echo "SELECT pg_terminate_backend(pid) FROM pg_stat_activity  WHERE pid <> pg_backend_pid() "' +
            ' | ' + self.create_psql_cmdline('postgres')
        )

    def get_all_databases_command(self):
        return self.fill_template(
            'echo "SELECT datname FROM pg_database WHERE datistemplate = false;"' +
            ' | ' + self.create_psql_cmdline('postgres') + ' | sed \'/^$/d\''
        )

    @staticmethod
    def create_psql_cmdline(db: str = '') -> str:
        return 'PGPASSWORD=%password% psql -t -U %user% -p %port% -h %host% ' + db

    def get_connection_limit_setter_command(self, database_name: str, limit: int):
        return self.fill_template(
            'echo "ALTER DATABASE ' + database_name + ' CONNECTION LIMIT ' + str(limit) + ';"' +
            ' | ' + self.create_psql_cmdline('postgres')
        )


class PostgreSQLBaseBackupDefinition(AbstractSQLDefinition):
    """
        PostgreSQL backup using pg_basedump
    """

    _base_backup_cmd: str
    _server_shutdown_cmd: str
    _server_start_cmd: str
    _temp_dir: str
    _pack_cmd: str
    _restore_dir: str
    _ownership: str

    def fill_template(self, template: str):
        return super().fill_template(template) \
            .replace('%temp_dir%', self._temp_dir) \
            .replace('%restore_dir%', self._restore_dir) \
            .replace('%old_dir%', self._old_dir)

    def _init_cmds(self, config: dict):
        self._base_backup_cmd = config.get('base_backup_cmd')
        self._server_shutdown_cmd = config['server_shutdown_cmd']
        self._server_start_cmd = config['server_start_cmd']
        self._restore_dir = config['restore_dir'].rstrip('/')
        self._old_dir = config['old_dir']
        self._temp_dir = config.get('temp_dir', '/tmp/.pg_basebackup')
        self._pack_cmd = config.get('pack_cmd', 'cd %temp_dir% && tar --exclude=".." --exclude="." -cvf - * .*')
        self._ownership = config.get('ownership', '')

        if not self._base_backup_cmd:
            self._base_backup_cmd = 'PGPASSWORD=%password% ' + \
                                    'pg_basebackup -X stream -h %host% -U %user% -v -D %temp_dir% -F plain'

    def get_backup_command(self) -> str:
        """ Uses pg_basebackup to dump all required database files and configuration files """

        return self.fill_template(
            self._base_backup_cmd + ' && ' + self._pack_cmd
        )

    def get_restore_command(self) -> str:
        """ Will unpack tar archive with database files """

        return self.fill_template('tar --same-owner -xf - -C %restore_dir%')

    def get_rename_data_dir_command(self) -> str:
        """ We want to keep the previous data in {{ old_dir }} directory, for safety """

        return self.fill_template('rm -rf %old_dir% && mv %restore_dir% %old_dir% && mkdir -p %restore_dir%')

    def get_rescue_command_on_failed_restore(self) -> str:
        return self.fill_template('mv %restore_dir% %restore_dir%.abort && mv %old_dir% %restore_dir%')

    def get_server_shutdown_command(self) -> str:
        return self._server_shutdown_cmd

    def get_server_start_command(self) -> str:
        return self._server_start_cmd

    def get_permissions_restore_command(self):
        if self._ownership:
            return self.fill_template('chown -R ' + self._ownership + ' %restore_dir%')

        return ''

    def get_make_tempdir_command(self) -> str:
        return self.fill_template('rm -rf %temp_dir% && mkdir -p %temp_dir%')

    def get_temporary_directory_clean_up_command(self) -> str:
        return self.fill_template('rm -rf %temp_dir%')

    @classmethod
    def from_config(cls, config: dict, name: str):
        definition = cls(
            access=config['access'],
            _type=config['type'],
            collection_id=config['collection_id'],
            encryption=config['encryption'],
            tar_pack_cmd=config.get('tar_pack_cmd', BackupDefinition._tar_pack_cmd),
            tar_unpack_cmd=config.get('tar_unpack_cmd', BackupDefinition._tar_unpack_cmd),
            host=config['host'],
            port=int(config['port']),
            user=config['user'],
            password=config['password'],
            database=config['database'],
            docker_bin=config.get('docker_bin', 'docker'),
            container=config.get('container', ''),
            name=name
        )

        definition._init_cmds(config)
        return definition
