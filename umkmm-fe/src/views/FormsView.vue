<script setup>
import { reactive, ref, onMounted } from 'vue'
import { mdiBallotOutline,  mdiDelete, mdiUpload } from '@mdi/js'
import SectionMain from '@/components/SectionMain.vue'
import CardBox from '@/components/CardBox.vue'
import FormCheckRadioGroup from '@/components/FormCheckRadioGroup.vue'
import FormFilePicker from '@/components/FormFilePicker.vue'
import FormField from '@/components/FormField.vue'
import FormControl from '@/components/FormControl.vue'
import BaseDivider from '@/components/BaseDivider.vue'
import BaseButton from '@/components/BaseButton.vue'
import BaseButtons from '@/components/BaseButtons.vue'
import SectionTitle from '@/components/SectionTitle.vue'
import LayoutAuthenticated from '@/layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@/components/SectionTitleLineWithButton.vue'
import NotificationBarInCard from '@/components/NotificationBarInCard.vue'
import CardBoxModal from '@/components/CardBoxModal.vue'
import axios from 'axios'
import OverlayLayer from '@/components/OverlayLayer.vue'
import { useRoute } from 'vue-router';

const selectOptions = [
  { id: 1, label: 'Business development' },
  { id: 2, label: 'Marketing' },
  { id: 3, label: 'Sales' }
]

const form = ref({
  name: '',
  desc: '',
  address: '',
  city: '',
  province: '',
  long: '',
  lat: '',
  owner: '',
  contact: '',
  photo: [],
  products: [{name: '', price: '', photo: []}]
})

const modalError = ref(false)

const modalSuccess = ref(false)

const modal = ref({})

function isObjectEmpty(obj) {
  for (const key in obj) {
    if (obj.hasOwnProperty(key)) {
      if (Array.isArray(obj[key])) {
        if (obj[key].some(item => isObjectEmpty(item))) {
          return true;
        }
      } else if (typeof obj[key] === 'object' && isObjectEmpty(obj[key])) {
        return true;
      } else if (obj[key] === '' || obj[key] === null || obj[key] === undefined) {
        return true;
      }
    }
  }
  return false;
}

const submit = () => {
  const isEmpty = isObjectEmpty(form.value);

  if(isEmpty){
    modal.value = {type: "Error", title: "Fill all the field"}
  }else{
    const formData = new FormData()
    formData.append('name', form.value.name)
    formData.append('desc', form.value.desc)
    formData.append('address', form.value.address)
    formData.append('city', form.value.city)
    formData.append('province', form.value.province)
    formData.append('long', form.value.long)
    formData.append('lat', form.value.lat)
    formData.append('owner', form.value.owner)
    formData.append('contact', form.value.contact)
    for (let i = 0; i <  form.value.photo.length; i++) {
      formData.append(`photo[${i}]`, form.value.photo[i].file)
    }
    for (let i = 0; i <  form.value.products.length; i++) {
      formData.append(`products[${i}][name]`, form.value.products[i].name)
      formData.append(`products[${i}][price]`, form.value.products[i].name)
      for (let o = 0; o <  form.value.products[i].photo.length; o++) {
        formData.append(`products[${i}][photo][${o}]`, form.value.products[i].photo[o].file)
      }
    }
    isLoading.value = true
    const id = route.params.id

    if(id){
      axios.post('http://127.0.0.1:8000/api/umkm/'+id, formData)
        .then(response => {
          const apiData = response.data;
          modal.value = {type: "Success", title: apiData.message}
          isLoading.value = false
          reset()
        })
        .catch(error => {
          console.log(error)
        })
    }else{
      axios.post('http://127.0.0.1:8000/api/umkm', formData)
        .then(response => {
          const apiData = response.data;
          modal.value = {type: "Success", title: apiData.message}
          isLoading.value = false
          reset()
        })
        .catch(error => {
          console.log(error)
        })
    }
  }
}

const reset = () => {
  form.value = {
    name: '',
    desc: '',
    address: '',
    city: '',
    province: '',
    long: '',
    lat: '',
    owner: '',
    contact: '',
    photo: [],
    products: [{name: '', price: '', photo: []}]
  }
}


const addProduct = () => {
  form.value.products.push({name: '', price: '', photo: []})
}


const handleFileChange = (event, component) => {
  const files = event.target.files;

  for (let i = 0; i < files.length; i++) {
    if (component.length < 3 && i < 3) {
      const reader = new FileReader();

      reader.onload = (e) => {
        component.push({ url: e.target.result, file: files[i] });
      };
      reader.readAsDataURL(files[i]);
    }
  }
}

const removeImage = (index, component) => {
  component.splice(index, 1);
}

const isLoading = ref(false)

const data = ref([])

const getCurrentData = (id) => { 
  isLoading.value = true
  axios.get('http://127.0.0.1:8000/api/umkm/'+id)
    .then(response => {
      const apiData = response.data;
      data.value = apiData
      form.value = apiData
      form.value.photo = []
      for (let i = 0; i < form.value.products.length; i++) {
        form.value.products[i].photo = []
      }
      isLoading.value = false
    })
    .catch(error => {
      isLoading.value = false
      
  })
}

const route = useRoute();

onMounted(() => {
  const id = route.params.id
  if(id){
    getCurrentData(id)
  }
});

</script>

<template>
  <LayoutAuthenticated>

    <OverlayLayer v-show="isLoading" @overlay-click="cancel">
      <CardBox
        v-show="isLoading"
        class="shadow-lg max-h-modal w-11/12 md:w-3/5 lg:w-2/5 xl:w-4/12 z-50"
        is-modal
      >
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: rgb(255, 255, 255); display: block; shape-rendering: auto;" width="100px" height="100px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
          <circle cx="50" cy="50" r="32" stroke-width="10" stroke="#1f2937" stroke-dasharray="50.26548245743669 50.26548245743669" fill="none" stroke-linecap="round">
            <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0 50 50;360 50 50"></animateTransform>
          </circle>
        </svg>
      </CardBox>
    </OverlayLayer>

    <CardBoxModal v-model="modal.type" :title="modal.title" :button="modal.type == 'Error' ? 'danger' : 'success'">
    </CardBoxModal>

    <!-- <CardBoxModal :modelValue="modal.type" :title="modal.title" button="success">
    </CardBoxModal> -->

    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiBallotOutline" title="UMKM Form" main>
      </SectionTitleLineWithButton>
      <CardBox is-form @submit.prevent="submit">
        <FormField label="UMKM Name">
          <FormControl v-model="form.name" placeholder="Your UMKM name" />
        </FormField>

        <FormField label="Description">
          <FormControl  v-model="form.desc"  type="textarea" placeholder="Explain your UMKM" />
        </FormField>

        <BaseDivider />

        <FormField label="Address">
          <FormControl v-model="form.address" type="text" placeholder="Your address" />
        </FormField>

        <FormField label="City">
          <FormControl v-model="form.city" type="text" placeholder="Your city" />
        </FormField>

        <FormField label="Province">
          <FormControl v-model="form.province" type="text" placeholder="Your province" />
        </FormField>

        <FormField label="Long">
          <FormControl v-model="form.long" type="text" placeholder="Your long map" />
        </FormField>

        <FormField label="Lat">
          <FormControl v-model="form.lat" type="text" placeholder="Your lat map" />
        </FormField>

        <BaseDivider />

        <FormField label="Owner">
          <FormControl v-model="form.owner" type="text" placeholder="Your name" />
        </FormField>

        <FormField label="Contact Number">
          <FormControl v-model="form.contact" type="tel" placeholder="Your contact number" />
        </FormField>

        <div class="block font-bold mb-4">UMKM Photos</div>
        <div class="flex items-stretch justify-start relative">
          <label class="inline-flex">
            <BaseButton
              as="a"
              :icon-size="undefined"
              label="Select Photos"
              :icon="mdiUpload"
              color="info"
            />
            <input
              ref="root"
              type="file"
              class="absolute top-0 left-0 w-full h-full opacity-0 outline-none cursor-pointer -z-1"
              @change="(e) => handleFileChange(e, form.photo)"
              multiple
            />
          </label>
          <div
            v-if="form.photo.length > 0"
            class="px-4 py-2 flex gap-4 bg-gray-100 dark:bg-slate-800 border-gray-200 dark:border-slate-700 border rounded-r"
          >
            <span 
              v-for="(image, index) in form.photo" 
             class="text-ellipsis line-clamp-1">
              {{ image.file.name }}
            </span>
          </div>
        </div>
        <div class="text-xs text-gray-500 dark:text-slate-400 mt-1">
          Max 3 items
        </div>
        <div class="flex gap-3 py-3">
          <div v-for="(image, index) in form.photo" :key="index" class="image-preview flex flex-col gap-3 align-items-center">
            <img :src="image.url" alt="Preview" class="w-20 h-20 rounded object-cover cursor-pointer" 
              @click="removeImage(index, form.photo)"
            />
          </div>
        </div>

        <BaseDivider />

        <div class="block font-bold mb-4">Products</div>

        <div v-for="(item, index) in form.products" :key="index" class="py-4">
          <FormField label="Name">
            <FormControl v-model="item.name" type="text" placeholder="Your product name" />
          </FormField>

          <FormField label="Price">
            <FormControl v-model="item.price" type="text" placeholder="Your contact number" />
          </FormField>

          <div class="block font-bold mb-4">Photo Products</div>
          <div class="flex items-stretch justify-start relative">
            <label class="inline-flex">
              <BaseButton
                as="a"
                :icon-size="undefined"
                label="Select Photos"
                :icon="mdiUpload"
                color="info"
              />
              <input
                ref="root"
                type="file"
                class="absolute top-0 left-0 w-full h-full opacity-0 outline-none cursor-pointer -z-1"
                @change="(e) => handleFileChange(e, item.photo)"
                multiple
              />
            </label>
            <div
              v-if="item.photo.length > 0"
              class="px-4 py-2 flex gap-4 bg-gray-100 dark:bg-slate-800 border-gray-200 dark:border-slate-700 border rounded-r"
            >
              <span 
                v-for="(image, index) in item.photo" 
              class="text-ellipsis line-clamp-1">
                {{ image.file.name }}
              </span>
            </div>
          </div>
          <div class="text-xs text-gray-500 dark:text-slate-400 mt-1">
            Max 3 items
          </div>
          <div class="flex gap-3 py-3">
            <div v-for="(image, index) in form.products[index].photo" :key="index" class="image-preview flex flex-col gap-3 align-items-center">
              <img :src="image.url" alt="Preview" class="w-20 h-20 rounded object-cover cursor-pointer" 
                @click="removeImage(index, item.photo)"
              />
            </div>
          </div>
        </div>
        
      
        <div class="flex justify-end">
          <BaseButton type="button" color="contrast" label="Add another product" @click="addProduct" />
        </div>

        <BaseDivider />

        <template #footer>
          <BaseButtons>
            <BaseButton type="submit" color="info" :label="route.params.id ? 'Update' :'Submit'" />
            <BaseButton type="reset" color="info" outline label="Reset" @click="reset" />
          </BaseButtons>
        </template>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
