

from ..entity.recovery import RecoveryPlan
from . import AbstractController
from .backup import BackupController


class SnapshotController(AbstractController):

    def perform(self, plan_name: str):
        self._logger.info('Performing a snapshot using "' + plan_name + '" plan')
        results = {True: [], False: []}

        plan = self._definition_factory.get_recovery_plan(plan_name)

        for name in plan.get_planned_definitions_to_recover_in_order():
            result = self._perform_recovery(name, plan)
            results[result].append(name)

        return {
            'success': results[True],
            'failure': results[False]
        }

    def _perform_recovery(self, definition_name: str, plan: RecoveryPlan) -> bool:
        controller = BackupController(
            self._definition_factory,
            self._logger,
            self._mapping,
            self._client,
            self._notifier
        )

        try:
            controller.perform(definition_name)
            return True

        except Exception as e:
            if not plan.on_error_should_continue():
                raise e

            return False

