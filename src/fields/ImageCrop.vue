<template>
  <k-field v-bind="$props" :input="_uid" class="kirby-imagecrop-field">
    <div class="image" v-if="image">
      <k-grid>
        <k-column width="5/6">
          <vue-cropper
            ref="cropper"
            :view-mode="1"
            dragMode="crop"
            :autoCrop="true"
            :zoomable="false"
            :movable="false"
            :data="value"
            :aspectRatio="aspectRatio"
            :src="image"
            alt="Source Image"
            :ready="onReady"
            :crop="cropmove"
            :cropend="cropend"    
          ></vue-cropper>
        </k-column>
        <k-column width="1/6">
					<k-headline>Crop Properties</k-headline>
					<k-items layout="list" v-if="hasFiber" class="imagecrop-dimensions">
					  <k-item layout="list" :image="{icon: '→',  back: 'black'}" class="k-imagecrop-icon">
							<k-number-input class="k-item-title" disabled :value="value.x" />
							<p class="k-item-info" >X</p>
						</k-item>
						<k-item layout="list" :image="{icon: '↓',  back: 'black'}" class="k-imagecrop-icon">
							<k-number-input class="k-item-title" disabled :value="value.y" />
							<p class="k-item-info" >Y</p>
						</k-item>
					  <k-item layout="list" :image="{icon: '↔️', back: 'black'}" class="k-imagecrop-icon">
							<k-number-input class="k-item-title" disabled :value="value.width" />
							<p class="k-item-info" >Width</p>
						</k-item>
					  <k-item layout="list" :image="{icon: '↕️', back: 'black'}" class="k-imagecrop-icon">
							<k-number-input class="k-item-title" disabled :value="value.height" />
							<p class="k-item-info" >Height</p>
						</k-item>
					</k-items>
					<k-list v-else>
					  <k-list-item :image=true :icon="{type: '→', back: 'black', emoji: true}" :text="value.x"/>
					  <k-list-item :image=true :icon="{type: '↓', back: 'black', emoji: true}" :text="value.y"/>
					  <k-list-item :image=true :icon="{type: '↔️', back: 'black', emoji: true}" :text="value.width"/>
					  <k-list-item :image=true :icon="{type: '↕️', back: 'black', emoji: true}" :text="value.height"/>
					</k-list>
        </k-column>
      </k-grid>
    </div>
    <k-box v-else>
      That's not an image!
    </k-box>
  </k-field>
</template>

<script>
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';

export default {
  components: {
    VueCropper
  },
  props: {
    label: String,
    name: String,
    image: String,
    value: Object,
    minSize: Object,
    preserveAspectRatio: Boolean
  },
  data() {
    return {
	  	isCropping: Boolean,
	  	ready: false,
	  }
  },
  computed: {
		hasFiber(){
			return (window && window.panel && window.panel.$languages);
		},
    data() {
      return this.value;
    },
    aspectRatio(){
      if(this.preserveAspectRatio){
        return this.minSize.width / this.minSize.height;
      } else {
        return NaN;
      }
    }
  },
  watch: {
    value: function(){
      if(!this.isCropping){
        this.$refs.cropper.setData(this.value);
      }
    }
  },
  methods: {
    cropmove(e){
      var
        update = false,
        data = this.$refs.cropper.getData(true);
      
      this.isCropping = true;

      if(data.width < this.minSize.width) {
        data.width = this.minSize.width;
        update = true;
      }
      if(data.height < this.minSize.height) {
        data.height = this.minSize.height;
        update = true;
      }

      //this.value = data;
      this.$emit('input', data);
      if(update){
        this.$refs.cropper.setData(data);
      }
    },
    cropend(e){
      if(this.ready){
        let value = this.$refs.cropper.getData(true);
        this.isCropping = false;
        this.$emit("input", value);
      }
    },
    onReady(){
      this.ready = true;
      this.isCropping = false;
    }
  }
};

</script>

<style lang="scss">
	.k-imagecrop-icon .k-item-figure {
		color: white;
	}
	.imagecrop-dimensions .k-item-title {
		width: 50%;
		min-width: 50px;
	}
</style>