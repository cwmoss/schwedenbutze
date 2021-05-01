<template>
  <Layout :show-logo="false">
    <!-- Author intro -->
    <author-card :show-title="true" />

    <template slot="sidebar">
      <navigation />
    </template>

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
  post: sanityPage (path: "/navigation") {
    title
    sections{
      title
      ref{
        ... on SanityPost{
        _type
        _id
        path
          title
          _rawBody(resolveReferences: {maxDepth: 5})
          mainImage{
            asset {
              _id
              url
            }
            caption
          }
        }
      }
    }
  }
}

</page-query>

<script>
import AuthorCard from '~/components/AuthorCard'
import PostCard from '~/components/PostCard'
import Navigation from '~/components/Navigation'

export default {
  components: {
    AuthorCard,
    PostCard,
    Navigation
  },
  metaInfo: {
    title: 'Välkommen till Zorros Südhof'
  }
}
</script>
