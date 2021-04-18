<template>
  <Layout :show-logo="false">
    <!-- kalender eimer
 :linkedCalendars="linkedCalendars"
:timePicker24Hour="timePicker24Hour"
:dateFormat="dateFormat"
    -->
    <author-card :show-title="true" />

    <date-range-picker v-if="ready" 
            ref="picker"
            :opens="'inline'"
            :locale-data="{ firstDay: 1, format: 'DD-MM-YYYY' }"
            :minDate="minDate" 
            :maxDate="maxDate"
            :singleDatePicker=false
            :timePicker=false
            :ranges=false
            :showWeekNumbers=true
            :showDropdowns=true
            :autoApply=false
            :closeOnEsc=false
				:date-format="mark_reserved"
            v-model="dateRange"
            @update="updateValues"
            @toggle="checkOpen" 
    >
        <template v-slot:input="picker" style="xmin-width: 350px;">
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
import { eachDayOfInterval, format } from 'date-fns'

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
		  ready: false,
		  reserved: {},
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
    },
	 mark_reserved(classes, date){
		if (!classes.disabled) {
			var d = format(date, 'yyyy/MM/dd')
			if(this.reserved[d] && this.reserved[d]==2) classes.disabled=true
		}
		return classes
	 },
	 setup_reserved(resv){
		var me = this
		var days = resv.map(function(e){
			console.log("el", e, new Date(e[0]), new Date(e[1]))
			return eachDayOfInterval({start: new Date(e[0]), end: new Date(e[1])})
		})
		console.log(days)
		days.forEach(function(els, i){
			var max = els.length-1
			els.forEach(function(e, j){
				var weight=(j==0 || j==max)?1:2
				var d = format(e, 'yyyy/MM/dd')
				if(me.reserved[d]) me.reserved[d]++
				else me.reserved[d]=weight
			})

		})
		console.log("res days", this.reserved)
		this.ready=true
	}
  },
	mounted: function () {
    var reserved = [['2021/03/14', '2021/03/17'], ['2021/03/19', '2021/03/21' ], ['2021/03/22', '2021/03/25'], ['2021/03/25', '2021/03/28']]
	 this.setup_reserved(reserved)
    console.log('mounted', reserved)
	
  }
}
</script>

<style>
/*  @import "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css";
*/
</style>