<template>
  <Layout :show-logo="false">
    <!-- 
		// v-model="calendarData"

    // v-on:changedMonth="changedMonth"
    // v-on:changedYear="changedYear"

    // :sundayStart="true"
    // :date-format="'dd/mm/yyyy'"
    // :is-date-range="true"
    // :is-date-picker="true"
    -->
    <author-card :show-title="true" />

	<FunctionalCalendar
		v-model="calendarData"
		:disabledDates="disabled_dates"
		:isDateRange="true"
		:isMultiple="true"
		:calendarsCount="2"
		:minSelDays="1" 
		:maxSelDays="60"
		:dateFormat="'yyyy-mm-dd'"
		@selectedDaysCount="range_selected"
	></FunctionalCalendar>

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
import { FunctionalCalendar } from 'vue-functional-calendar';



export default {
  components: { 
    FunctionalCalendar,
    AuthorCard
  },
  metaInfo: {
    title: 'Kalender'
  },
  data () {
      return {
		  ready: false,
		  calendarData: {},
		  disabled_dates: ['beforeToday', '1/4/2021', '2/4/2021'],
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
	range_selected(ev){
		console.log("range selected", ev, this.calendarData.dateRange, this.calendarData)
	},
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
.vfc-disabled{
	text-decoration:line-through;
}
</style>