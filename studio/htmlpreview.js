import S from '@sanity/desk-tool/structure-builder'
import React from 'react'
import BlockContent from '@sanity/block-content-to-react'

export const HtmlPreview = ({document}) => {
	return (
  		<div style={{padding:'16px'}}>
	  		<h2>{document.displayed.title}</h2>
	  		<BlockContent
	        blocks={document.displayed.body}
	        className="plain-block-content"
	        renderContainerOnSingleChild
	        
	      />
  		</div>
	)
}