<template>
</template>

<script>
export default {
    components: {},
    mounted() {
        this.$auth().logout()
        this.$router.push('Login')
    }
}
</script>
