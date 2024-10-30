<template>
	<div v-if="loadingCover" style="z-index: 510; margin-left: -6rem;"
		class="d-flex bg-secondary justify-content-center align-items-center position-fixed h-100 w-100 bg-opacity-25">
		<div class="spinner-border text-primary" style="z-index: 511;" role="status">
			<span class="visually-hidden">Loading...</span>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		loadingCover: {
			type: Boolean,
			default: false
		}
	}
}
</script>
