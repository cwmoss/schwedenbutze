<template>
  <Layout :show-logo="false">
    <!-- kalender eimer
 :linkedCalendars="linkedCalendars"
:timePicker24Hour="timePicker24Hour"
:dateFormat="dateFormat"
    -->
    <author-card :show-title="true" />

    <date-range-picker
            ref="picker"
            :opens="'inline'"
            :locale-data="{ firstDay: 1, format: 'DD-MM-YYYY' }"
            :minDate="minDate" 
            :maxDate="maxDate"
            :singleDatePicker=false
            :timePicker=false
            
            :showWeekNumbers=true
            :showDropdowns=true
            :autoApply=false
            :closeOnEsc=false
            v-model="dateRange"
            @update="updateValues"
            @toggle="checkOpen" 
    >
        <template v-slot:input="picker" style="min-width: 350px;">
            {{ picker.startDate |custdate }} - {{ picker.endDate |custdate}}
        </template>
    </date-range-picker>

  </Layout>
</template>

<page-query>
{
  metadata {
    sanityOptions {
      projectId
      dataset
    }
  }
  
}

</page-query>

<script>
import AuthorCard from '~/components/AuthorCard'
import DateRangePicker from 'vue2-daterange-picker'

import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'

export default {
  components: { 
    DateRangePicker,
    AuthorCard
  },
  metaInfo: {
    title: 'Kalender'
  },
  data () {
      return {
        minDate: "2021-02-01",
        maxDate: "2024-06-30",
        opens: true,
        dateRange: {
          startDate: null,
          endDate: null,
        },
      }
  },
  methods:{
    updateValues(){
      console.log("update")
    },
    checkOpen(){
      return true
    }
  }
}
</script>
