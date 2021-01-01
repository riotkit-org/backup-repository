<template>
  <div class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <edit-profile-form>
          </edit-profile-form>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import EditProfileForm from './UserProfile/EditProfileForm.vue'

  export default {
    components: {
      EditProfileForm
    }
  }
</script>
