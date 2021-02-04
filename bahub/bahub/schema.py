"""
JSON Schema helper methods
==========================
"""

from typing import Dict


def create_example_from_attributes(attributes: dict) -> Dict[str, str]:
    """
    Creates an example usage - part of YAML document basing on the schema

    :param attributes:
    :return:
    """

    as_dict = {}

    for key, attribute in attributes.items():
        as_dict[key] = attribute['example'] if 'example' in attribute else attribute['type']

        if 'properties' in attribute:
            as_dict[key] = create_example_from_attributes(attribute['properties'])

    return as_dict
