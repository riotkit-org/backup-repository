/**
 * App Plugin
 * ==========
 *
 * Registers core services to be initialized and accessible in global scope of each page
 */

// @ts-ignore
import AuthenticatorService from "../services/authenticator.service.ts";
// @ts-ignore
import BackupRepositoryBackend from "src/services/backend.service.ts";
// @ts-ignore
import AuthBackend from "src/services/auth.backend.service.ts";
// @ts-ignore
import BackupCollectionBackendService from "src/services/backup-collection.backend.service.ts";


const AppPlugin = {
    install(Vue, options) {
        Vue.prototype.$backend = function () {
            return new BackupRepositoryBackend(this)
        };

        Vue.prototype.$authBackend = function () {
            return new AuthBackend(this)
        };

        Vue.prototype.$backupCollectionBackend = function () {
            return new BackupCollectionBackendService(this)
        };

        Vue.prototype.$auth = function () {
            return new AuthenticatorService(this.$store)
        };

        Vue.prototype.debounce = function (id, func, delay) {
            if (!globalThis.toDebounce) {
                globalThis.toDebounce = {}
            }

            return function() {
                const context = this;
                const args = arguments;

                if (globalThis.toDebounce[id]) {
                    clearTimeout(globalThis.toDebounce[id]);
                }

                globalThis.toDebounce[id] = setTimeout(() => func.apply(context, args), delay);
            };
        }
    }
};

export default AppPlugin;
