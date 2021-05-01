<template>
    <!-- List sections -->
    <div class="z-nav">
        <ul>
            <li v-for="section in $static.navigation.sections" :key="section.ref._id">
                <g-link :to="section.ref.path">
                {{ section.title || section.ref.title }}
                </g-link></li>
            <li><g-link to="/booking">Buchung</g-link></li>
        </ul>
    </div>
</template>

<static-query>
{
  metadata {
    sanityOptions {
      projectId
      dataset
    }
  }
  navigation: sanityPage (path: "/navigation") {
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

</static-query>

<style lang="scss">
.z-nav {
  ul {
    margin: 0;
    padding: 0;
    list-style: none;
  }

  li { 
    float: left;
    }

  a {
    display: block;
    padding: 6px 12px;
    text-decoration: none;
    font-weight: 600;
    color: var(--bg-color);
  }
}
</style>
