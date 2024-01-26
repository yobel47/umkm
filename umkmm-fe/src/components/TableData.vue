<script setup>
import { computed, ref, onMounted  } from 'vue'
import { useMainStore } from '@/stores/main'
import { mdiEye, mdiTrashCan, mdiPencil } from '@mdi/js'
import CardBoxModal from '@/components/CardBoxModal.vue'
import TableCheckboxCell from '@/components/TableCheckboxCell.vue'
import BaseLevel from '@/components/BaseLevel.vue'
import BaseButtons from '@/components/BaseButtons.vue'
import BaseButton from '@/components/BaseButton.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import axios from 'axios'
import BaseDivider from './BaseDivider.vue'

defineProps({
  checkable: Boolean
})

const mainStore = useMainStore()

const items = computed(() => mainStore.clients)

const isModalActive = ref(false)

const isModalDangerActive = ref(false)

const isLoading = ref(false)

const perPage = ref(5)

const currentPage = ref(0)

const checkedRows = ref([])

const itemsPaginated = computed(() =>
  data.value.slice(perPage.value * currentPage.value, perPage.value * (currentPage.value + 1))
)

const numPages = computed(() => Math.ceil(data.value.length / perPage.value))

const currentPageHuman = computed(() => currentPage.value + 1)

const pagesList = computed(() => {
  const pagesList = []

  for (let i = 0; i < numPages.value; i++) {
    pagesList.push(i)
  }

  return pagesList
})

const remove = (arr, cb) => {
  const newArr = []

  arr.forEach((item) => {
    if (!cb(item)) {
      newArr.push(item)
    }
  })

  return newArr
}

const data = ref([])

const getCurrentData = () => { 
  isLoading.value = true
  axios.get('http://127.0.0.1:8000/api/umkm')
    .then(response => {
      const apiData = response.data;
      data.value = apiData
      isLoading.value = false
    })
    .catch(error => {
      isLoading.value = false
      
  })
}

onMounted(() => {
  getCurrentData();
});

const modalData = ref({})

const showInfo = (index) => {
  isModalActive.value = true
  modalData.value = data.value[index]
}

const showDelete = (index) => {
  isModalDangerActive.value = true
  id.value = data.value[index].id
}

const id = ref()

const onDelete = (index) => {
  isModalDangerActive.value = false
  isLoading.value = true
  axios.delete(`http://127.0.0.1:8000/api/umkm/${id.value}`)
    .then(response => {
      const apiData = response.data;
      data.value = apiData
      isLoading.value = false
      getCurrentData()
    })
    .catch(error => {
      isLoading.value = false
      
  })
}


</script>

<template>
  <CardBoxModal v-model="isModalActive" :title="modalData.name" buttonLabel="Okay">
    <p><b>Address</b> : {{ modalData.address }}, {{ modalData.city }}, {{ modalData.province }}</p>
    <p><b>Owner</b> : {{ modalData.owner }}</p>
    <p><b>Contact</b> : {{ modalData.contact }}</p>
    <div v-if="modalData.photo" class="flex gap-3" >
      <div  v-for="item in JSON.parse(modalData.photo)" >
        <img :src="item" class="w-20 h-20 rounded"  />
      </div>
    </div>
    <BaseDivider />

    <p class="text-lg"><b>Products</b></p>
    <BaseDivider />

    <div v-for="item in modalData.products" >
      <p><b>Name</b> : {{ item.name }}</p>
      <p><b>Price</b> : {{ item.name }}</p>
      <div v-if="modalData.photo" class="flex gap-3 py-2" >
        <div v-for="item in JSON.parse(modalData.photo)" >
          <img :src="item" class="w-20 h-20 rounded"  />
        </div>
      </div>
      <BaseDivider />
    </div>


  </CardBoxModal>

  <CardBoxModal v-model="isModalDangerActive" title="Please confirm" button="danger" @confirm="onDelete" has-cancel>
    <p>Are you sure to <b>delete</b> this UMKM?</p>
  </CardBoxModal>
  <div v-if="isLoading" class="py-10">
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="100px" height="100px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
      <circle cx="50" cy="50" r="32" stroke-width="10" stroke="#1f2937" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
      </circle>
    </svg>
  </div>

  <div class="py-10 px-5" v-else>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>City</th>
          <th>Province</th>
          <th>Owner</th>
          <th>Contact</th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in itemsPaginated" :key="data.id">
          <td data-label="Name">
            {{ item.name }}
          </td>
          <td data-label="City">
            {{ item.city }}
          </td>
          <td data-label="Province">
            {{ item.province }}
          </td>
          <td data-label="Owner">
            {{ item.owner }}
          </td>
          <td data-label="Contact">
            {{ item.contact }}
          </td>
          <td class="before:hidden lg:w-1 whitespace-nowrap">
            <BaseButtons type="justify-start lg:justify-end" no-wrap>
              <BaseButton color="info" :icon="mdiEye" small @click="showInfo(index)" />
              <BaseButton color="success" :icon="mdiPencil" small :to="`update/`+item.id" />
              <BaseButton
                color="danger"
                :icon="mdiTrashCan"
                small
                @click="showDelete(index)"
              />
            </BaseButtons>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
      <BaseLevel>
        <BaseButtons>
          <BaseButton
            v-for="page in pagesList"
            :key="page"
            :active="page === currentPage"
            :label="page + 1"
            :color="page === currentPage ? 'lightDark' : 'whiteDark'"
            small
            @click="currentPage = page"
          />
        </BaseButtons>
        <small>Page {{ currentPageHuman }} of {{ numPages }}</small>
      </BaseLevel>
    </div>
  </div>
  
</template>
