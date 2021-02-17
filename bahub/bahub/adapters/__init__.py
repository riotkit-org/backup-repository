"""
Backup Adapters
===============

Provides a dedicated backup handlers. Each backup handler is specialized in its own domain.

Example of handlers:
  - MySQL database handler
  - Filesystem handler
  - Redis handler
  - Remote Mail Handler
"""

from .filesystem import Adapter as FSAdapter
from .mysql import Adapter as MySQLAdapter
from .postgres_dump import Adapter as PostgresDumpAdapter


def adapters() -> list:
    return [FSAdapter, MySQLAdapter, PostgresDumpAdapter]
