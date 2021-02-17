from rkd.api.testing import BasicTestingCase
from bahub.schema import create_example_from_attributes


class TestSchema(BasicTestingCase):
    def test_create_example_from_attributes(self):
        """
        Given there are JSON schema object attributes
        Need: create an example usage basing on the schema

        :return:
        """

        example = {
            'field1': {
                'type': 'string'
            },
            'field2': {
                'type': 'string',
                'example': 'login123'
            },
            'field3': {
                'type': 'object',
                'properties': {
                    'subfield3.1': {
                        'type': 'string',
                        'example': 'Rojava'
                    }
                }
            }
        }

        self.assertEqual(
            {
                'field1': 'string',
                'field2': 'login123',
                'field3': {
                    'subfield3.1': 'Rojava'
                }
            },
            create_example_from_attributes(example)
        )
