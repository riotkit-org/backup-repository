<template>
    <div class="content">
        <div class="container-fluid">
            <form>
                <div class="simple-login-container">
                    <h2>A logging in is required to continue</h2>
                    <div class="row" v-if="!useJWT">
                        <div class="col-md-12 form-group">
                            <input type="text" class="form-control" placeholder="Email*" v-model="email">
                        </div>
                    </div>
                    <div class="row" v-if="!useJWT">
                        <div class="col-md-12 form-group">
                            <input type="password" placeholder="Enter your password*"
                                   name="password" class="form-control" id="passwordField"
                                   v-model="password" @blur="verifyPasswordValueChange">
                        </div>
                    </div>
                    <div class="row" v-if="useJWT">
                        <div class="col-md-12 form-group">
                            <textarea v-model="inputJWT" placeholder="Encoded JSON Web Token" class="form-control" rows="15"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <div class="form-check form-switch">
                                <label class="switch" style="float: left;">
                                    <input type="checkbox" class="default" v-model="useJWT" data-field="Use JSON Web Token">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-9 jwt-text">
                            Use JSON Web Token
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <input type="submit" class="btn btn-block btn-login" value="Log-in" @click.prevent="login">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    components: {},
    data () {
        return {
            email: '',
            password: '',
            inputJWT: '',
            useJWT: false
        }
    },
    methods: {
        login() {
            let that = this

            if (this.inputJWT && this.useJWT) {
                that.$auth().setAuthentication(this.inputJWT)
                that.$router.push('Overview')
                return
            }

            this.$backend().getJWT(this.email, this.password).then(function (token) {
                if (token) {
                    that.$auth().setAuthentication(token)
                    that.$router.push('Overview')
                }
            })
        },

        // https://github.com/vuejs/vue/issues/7058
        verifyPasswordValueChange() {
            let domElement = document.getElementById('passwordField')

            if (domElement.value !== this.password) {
                this.password = domElement.value
            }
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

.jwt-text {
    padding-top: 5px;
}

/* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    float:right;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input.default:checked + .slider {
    background-color: #444;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}
</style>
