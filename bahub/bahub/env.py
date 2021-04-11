import os
from rkd.env import STR_BOOLEAN_TRUE


def is_curl_debug_mode() -> bool:
    return os.getenv('RKD_CURL_DEBUG', '').lower() in STR_BOOLEAN_TRUE
