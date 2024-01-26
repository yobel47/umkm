<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDarkModeStore } from '@/stores/darkMode.js'
import { gradientBgPurplePink } from '@/colors.js'
import SectionMain from '@/components/SectionMain.vue'
import CardBox from '@/components/CardBox.vue'
import LayoutGuest from '@/layouts/LayoutGuest.vue'
import { MapboxMap, MapboxMarker } from '@studiometa/vue-mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import 'vue-search-select/dist/VueSearchSelect.css'
import { ModelSelect } from 'vue-search-select'
import axios from 'axios'
import BaseDivider from '@/components/BaseDivider.vue'
import BaseButton from '@/components/BaseButton.vue'

const styles = ['white', 'basic']

const darkModeStore = useDarkModeStore()

darkModeStore.set(false)

const router = useRouter()

const handleStyleChange = (slug) => {
  document.documentElement.classList.forEach((token) => {
    if (token.indexOf('style') === 0) {
      document.documentElement.classList.replace(token, `style-${slug}`)
    }
  })

  router.push('/login')
}

const data = ref([])

const item = ref({
  value: '',
  text: ''
})

const getUmkm = (e) => {
  axios.get('http://127.0.0.1:8000/api/umkm/search/'+e)
    .then(response => {
      const apiData = response.data;
      data.value = apiData.map(item => ({ value: item.id, text: item.name }));
    })
    .catch(error => {
      
  })
}

const dataMap = ref([])


const getCurrentData = () => { 
  axios.get('http://127.0.0.1:8000/api/umkm')
    .then(response => {
      const apiData = response.data;
      dataMap.value = apiData
    })
    .catch(error => {
      
  })
}

onMounted(() => {
  getCurrentData();
});


</script>

<template>
  <LayoutGuest>
    <div :class="gradientBgPurplePink" class="flex flex-col min-h-screen items-center justify-center">
        <div class="pb-10 md:text-5xl text-center text-white font-bold mt-12 mb-3 lg:mt-0">
          <p class="pb-8">Search UMKM : </p>
          <model-select :options="data"
              v-model="item"
              @searchchange="getUmkm"
              placeholder="Search UMKM">
         </model-select>
        </div>
          <MapboxMap
            style="height: 400px; width: 1000px"
            access-token="pk.eyJ1IjoieW9iZWwiLCJhIjoiY2xydTg2ajIwMDcwNTJ4cHF6MHE4d2Y3bSJ9.HufgqEMg-u96xqdyARnO-w"
            map-style="mapbox://styles/mapbox/streets-v11" 
          >
          <div v-for="(itemumkm, index) in dataMap" :key="index">
            <MapboxMarker :lng-lat="[0, 0]" popup>
              <template v-slot:popup>
                <p><b>Name</b> : {{ itemumkm.name }}</p>
                <p><b>Address</b> : {{ itemumkm.address }}, {{ itemumkm.city }}, {{ itemumkm.province }}</p>
                <p><b>Owner</b> : {{ itemumkm.owner }}</p>
                <p><b>Contact</b> : {{ itemumkm.contact }}</p>
                <div v-if="itemumkm.photo" class="flex gap-3" >
                  <div  v-for="item in JSON.parse(itemumkm.photo)" >
                    <img :src="item" class="w-20 h-20 rounded"  />
                  </div>
                </div>
                <BaseDividerr />

                <p class="text-lg"><b>Products</b></p>

                <div v-for="item in itemumkm.products" >
                  <p><b>Name</b> : {{ item.name }}</p>
                  <p><b>Price</b> : {{ item.name }}</p>
                  <div v-if="itemumkm.photo" class="flex gap-3 py-2" >
                    <div v-for="item in JSON.parse(itemumkm.photo)" >
                      <img :src="item" class="w-20 h-20 rounded"  />
                    </div>
                  </div>
                </div>
              </template>
            </MapboxMarker>
          </div>
            
          </MapboxMap>
          <BaseButton class="absolute top-0 right-0 m-8" type="button" color="contrast" label="Login" to="login" />

    </div>
  </LayoutGuest>
</template>
<style>
@import "vue-select/dist/vue-select.css";
</style>