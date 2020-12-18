<template>
    <div class="content">
        <div class="container-fluid">
            <div class="simple-login-container">
                <h2>A logging in is required to continue</h2>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <input type="text" class="form-control" placeholder="Email*" v-model="email">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <input type="password" placeholder="Enter your password*" class="form-control" v-model="password">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <input type="submit" class="btn btn-block btn-login" value="Log-in" @click.prevent="login">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    components: {},
    data () {
        return {
            email: 'example@riseup.net',
            password: 'aNti_cap.italiSM'
        }
    },
    methods: {
        login() {
            let that = this
            this.$backend().getJWT(this.email, this.password).then(function (token) {
                if (token) {
                    that.$auth().setAuthentication(token)
                    that.$router.push('Overview')
                }
            })
        }
    }
}
</script>

<style>
.simple-login-container{
    width:300px;
    max-width:100%;
    margin:50px auto;
}
.simple-login-container h2{
    text-align:center;
    font-size:20px;
}

.simple-login-container .btn-login{
    border-color: transparent;
    background-color: #ff5959;
    color:#fff;
}
</style>
