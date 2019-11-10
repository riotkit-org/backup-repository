from ..exceptions import ConfigurationFactoryException


class RecoveryPlan:
    _plan = []
    _policy = ""

    def __init__(self, plan, policy: str, all_available_services: list):
        self._plan = plan
        self._policy = policy

        if plan == "all" or plan == "*":
            self._plan = all_available_services

        self._validate_against(all_available_services)

    @staticmethod
    def from_config(config: dict, all_available_services: list):

        if config['policy'] not in ['restore-whats-possible', 'stop-on-first-error']:
            raise KeyError('Unsupported policy selected "' + str(config['policy']) + '"')

        return RecoveryPlan(config['definitions'], config['policy'], all_available_services)

    def get_planned_definitions_to_recover_in_order(self):
        return self._plan

    def on_error_should_continue(self) -> bool:
        return self._policy == 'restore-whats-possible'

    def _validate_against(self, all_available_services: list):
        for name in self._plan:
            if name not in all_available_services:
                raise ConfigurationFactoryException(
                    'Recovery plan refers to undefined backup definition "' + name + '"' +
                    ', possible options: ' + str(all_available_services)
                )
