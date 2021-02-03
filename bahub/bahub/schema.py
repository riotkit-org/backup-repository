"""
JSON Schema helper methods
==========================
"""


def list_attributes(attributes: dict):
    as_dict = {}

    for key, attribute in attributes.items():
        as_dict[key] = attribute['example'] if 'example' in attribute else attribute['type']

        if 'properties' in attribute:
            as_dict[key] = list_attributes(attribute['properties'])

    return as_dict
