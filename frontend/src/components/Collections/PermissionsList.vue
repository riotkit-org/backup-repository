<template>
    <card>
        <h4 slot="header" class="card-title">Edit permissions</h4>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group input-group">
                    <label class="control-label">Strategy</label>
                    <input type="text" class="form-control">
                </div>
            </div>
        </div>
    </card>
</template>

<script>
export default {
    props: {
        collection: {}
    }
}
</script>
