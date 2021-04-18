<template>
  <Layout :show-logo="false">

    <author-card :show-title="true" />

  <h1 class="post-title__text">{{ $page.post.title }}</h1>

  <div class="intro">

    <block-content
                  class="post__content"
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

	<button :disabled="!range_ok">Go on</button>

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
import AuthorCard from '~/components/AuthorCard'
import VRangeSelector from 'vuelendar/components/vl-range-selector'



export default {
  components: {
    VRangeSelector,
    AuthorCard,
    BlockContent
  },
  metaInfo: {
    title: 'Kalender'
  },
  data () {
      return {
		  ready: false,
			range: {},
			range_ok: false,
		  reserved: {}
      }
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
    var reserved = [['2021/03/14', '2021/03/17'], ['2021/03/19', '2021/03/21' ],
			['2021/03/22', '2021/03/25'], ['2021/03/25', '2021/03/28']]
	 this.setup_reserved(reserved)
    console.log('mounted', reserved)

  }
}
</script>

<style>


.disabled{
	text-decoration:line-through;
}
.selected.disabled{
	text-decoration:none;
}
</style>
