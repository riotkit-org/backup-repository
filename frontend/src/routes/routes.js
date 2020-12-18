import DashboardLayout from '../layout/DashboardLayout.vue'
// GeneralViews
import NotFound from '../pages/NotFoundPage.vue'

// Admin pages
import Overview from 'src/pages/Overview.vue'
import UserProfile from 'src/pages/UserProfile.vue'
import Icons from 'src/pages/Icons.vue'
import Notifications from 'src/pages/Notifications.vue'

import Login from 'src/pages/Login.vue'
import Logout from 'src/pages/Logout.vue';
import Users from 'src/pages/Users.vue'

const routes = [
    {
        path: '/',
        component: DashboardLayout,
        redirect: '/admin/overview'
    },
    {
        path: '/admin',
        component: DashboardLayout,
        redirect: '/admin/overview',
        children: [
            {
                path: 'overview',
                name: 'Overview',
                component: Overview
            },
            {
                path: 'users',
                name: 'Users',
                component: Users
            },
            {
                path: 'user/*',
                name: 'User profile',
                component: UserProfile
            },
            {
                path: 'user',
                name: 'Create new user',
                component: UserProfile
            },
            {
                path: 'login',
                name: 'Authenticate',
                component: Login
            },
            {
                path: 'logout',
                name: 'Logout',
                component: Logout
            },
            {
                path: 'icons',
                name: 'Icons',
                component: Icons
            },
            {
                path: 'notifications',
                name: 'Notifications',
                component: Notifications
            }
        ]
    },
    {path: '*', component: NotFound}
]

/**
 * Asynchronously load view (Webpack Lazy loading compatible)
 * The specified component must be inside the Views folder
 * @param  {string} name  the filename (basename) of the view to load.
 function view(name) {
   var res= require('../components/Dashboard/Views/' + name + '.vue');
   return res;
};**/

export default routes
