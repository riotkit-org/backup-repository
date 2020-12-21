/**
 * App Plugin
 * ==========
 *
 * Registers core services to be initialized and accessible in global scope of each page
 */

// @ts-ignore
import AuthenticatorService from "../services/authenticator.service.ts";

// @ts-ignore
import BackupRepositoryBackend from "../services/backend.service.ts";


const AppPlugin = {
    install(Vue, options) {
        Vue.prototype.$backend = function () {
            return new BackupRepositoryBackend(this)
        };

        Vue.prototype.$auth = function () {
            return new AuthenticatorService(this.$store)
        };
    }
};

export default AppPlugin;
