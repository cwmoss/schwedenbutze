<template>
  <Layout :show-logo="false">

<div class="post-title">
      <h1 class="post-title__text">{{ $page.post.title }}</h1>

    </div>

<div class="post content-box booking-page">
<div class="post__content">

  <div v-show="error" v-html="error" class="error"></div>

  <div v-if="step==1" class="step1">

    <div class="intro">

      <block-content

                  :blocks="$page.post.intro"
                  v-if="$page.post.intro"
                />

    </div>

    <v-range-selector v-if="ready"
      :start-date.sync="range.start"
      :end-date.sync="range.end"
      :is-disabled="is_reserved"
      @update:startDate="range_selected"
      @update:endDate="range_selected"
    />

    <button :disabled="!range_ok" @click="next()">Weiter</button>

  </div>

  <div v-if="step==2" class="step2">

    <h3 v-html="nice_range"></h3>
<form v-on:submit.prevent @submit="save()">
    <p>
    <label for="name">Dein Name:</label>
    <input id="name" name="name" v-model="reservation.name" required>
    </p>

    <p>
    <label for="email">Deine E-Mail-Adresse:</label>
    <input id="email" name="email" type="email" v-model="reservation.email" required>
    </p>

    <p>
    <label for="message">Nachricht (optional):</label>
    <textarea id="message" name="message" v-model="reservation.message"></textarea>
    </p>

    <input id="auto" name="auto" type="text" v-model="reservation.auto" class="auto_me" >

    <button :disabled="!save_ok">Buchung abschicken!</button>
</form>

  </div>

  <div v-if="step==3" class="step2">

    <h2>Danke! Deine Buchung ist angekommen!</h2>

  </div>
</div>
</div>

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
  post: sanityPost (path: "/booking-content") {
    title
    intro: _rawExcerpt(resolveReferences: {maxDepth: 5})
  }
}

</page-query>

<script>

import * as util from '../utils/util'

import BlockContent from '~/components/BlockContent'

import VRangeSelector from 'vuelendar/components/vl-range-selector'



export default {
  components: {
    VRangeSelector,

    BlockContent
  },
  metaInfo: {
    title: 'Kalender'
  },
  data () {
      return {
        step: 1,
        error: "",
        ready: false,
        saveing: false,
        range: {},
        range_ok: false,
        reserved: {},
        reservation:{name:"", email:"", message:"", auto:""}
      }
  },
  computed:{
    nice_range: function(){
      return util.format_interval(this.range.start, this.range.end)
    },
    save_ok(){
     var r = {name: this.reservation.name, email: this.reservation.email, message: this.reservation.message}
     return (!this.saveing && r.name && r.email && r.email.match(/.+@.+/))
   },
  },
  methods:{
	/*
		range_select will be called on every select event
		be it start or be it end
		so we have to make a snapshot on the values before
		evaluating because every tick counts
	*/
	range_selected(date){
		// start, end is the snapshot
		var start = this.range.start, end = this.range.end

		if(!date || !start || !end || (start==end) || (end<start)){
			this.range_ok=false
			return
		} else {
			this.range_ok=util.days_strings(start, end)
				.every(e=>!this.is_reserved(e))
		}
	},
	 is_reserved(date){
		if(date<util.format_mysql(new Date)) return true
		if(this.reserved[date] && this.reserved[date]==2) return true
		return false
	 },
   next(){
     this.step=2
   },
   save(){
     var vm = this
     vm.saveing = true
     var r = {
        name: this.reservation.name, email: this.reservation.email,
        message: this.reservation.message,
        arrival: this.range.start,
        departure: this.range.end,
        auto: this.reservation.auto
     }
     console.log('++ saveing ++', r)
     if(r.auto){
        vm.saveing = false
        vm.step = 3
        return
     }
     var request = new Request("/.netlify/functions/post-reservation", {
          method: 'POST',
          body: JSON.stringify(r),
          headers: new Headers()
      })
      fetch(request)
       .then((resp) => resp.json())
      .then(function(data) {
        console.log("rsp data", data)
        vm.saveing = false
        vm.step = 3
      })
      .catch(function(e){
        console.log("++ post fail", e)
        vm.error = e.message
        vm.saveing = false
      })
   },
	 setup_reserved(resv){
		var me = {}
		var reserved = resv.map(function(e){
			console.log("el", e, new Date(e[0]), new Date(e[1]))
			return util.days_strings(e[0], e[1])
		})
		.reduce(function(res, days){
			var last = days.length-1
			days.forEach(function(e, i){
				var weight=(i==0 || i==last)?1:2

				if(res[e]) res[e]++
				else res[e]=weight
			})
			return res
		}, {})

		console.log("res days", this.reserved)
		this.reserved = reserved
		this.ready=true
	}
  },
	mounted: function () {
    var vm = this
    var reserved = [['2021/03/14', '2021/03/17'], ['2021/03/19', '2021/03/21' ],
			['2021/03/22', '2021/03/25'], ['2021/03/25', '2021/03/28']]
    fetch("/.netlify/functions/fetch-reservations")
    .then((resp) => resp.json())
    .then(function(data) {
      console.log("rsp data", data)
      vm.setup_reserved(data.rsrv)
    })
    .catch(function(e){
      console.log("++ catch", e)
      vm.error = "could not fetch reserved dates"
    })

    console.log('mounted', reserved)

  }
}
</script>

<style lang="scss">



.vl-calendar-month__day.selected.disabled{
  color: white;
}
.disabled{
	text-decoration:line-through;
}
.selected.disabled{
	text-decoration:none;
}
.auto_me{
   opacity: 0;
   position: absolute;
   top: 0;
   left: 0;
   height: 0;
   width: 0;
   z-index: -1;
}


</style>
