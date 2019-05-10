

from ..entity.recovery import RecoveryPlan
from . import AbstractController
from .restore import RestoreController


class RecoverFromDisasterController(AbstractController):

    def perform(self, plan_name: str):
        self._logger.info('Recovery from disaster using "' + plan_name + '" recovery plan')
        results = {True: [], False: []}

        plan = self._definition_factory.get_recovery_plan(plan_name)

        for name in plan.get_planned_definitions_to_recover_in_order():
            result = self._perform_recovery(name, plan)
            results[result].append(name)

        return {
            'recovered': results[True],
            'not_recovered': results[False]
        }

    def _perform_recovery(self, definition_name: str, plan: RecoveryPlan) -> bool:
        controller = RestoreController(
            self._definition_factory,
            self._logger,
            self._mapping,
            self._client,
            self._notifier
        )

        self._logger.info('Performing recovery of "' + definition_name + '"')

        try:
            controller.perform(definition_name, 'latest')
            return True

        except Exception as e:
            if not plan.on_error_should_continue():
                raise e

            return False

