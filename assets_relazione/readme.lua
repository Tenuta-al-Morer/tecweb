function format_with_breaks(inlines)
  local html_str = ""
  for i, el in ipairs(inlines) do
    if el.t == 'LineBreak' then
      html_str = html_str .. "<br/>"
    elseif el.t == 'Space' then
      html_str = html_str .. " "
    elseif el.t == 'Str' then
      html_str = html_str .. el.text
    elseif el.t == 'Link' then
      html_str = html_str .. '<a href="' .. el.target .. '">' .. pandoc.utils.stringify(el.content) .. '</a>'
    elseif el.t == 'Strong' then
      html_str = html_str .. '<strong>' .. pandoc.utils.stringify(el.content) .. '</strong>'
    else
      html_str = html_str .. pandoc.utils.stringify(el)
    end
  end
  return html_str
end

function Div(el)
  if el.classes:includes('center') then
    local new_content = {}
    
    table.insert(new_content, pandoc.RawBlock('html', '<div align="center">'))

    for i, block in ipairs(el.content) do
      
      if block.t == "Para" and block.content[1] and block.content[1].t == "Image" then
        local img = block.content[1]
        local clean_src = img.src:gsub("\\", "/")
        clean_src = clean_src:gsub(" ", "%%20")
        
        -- Rimossi i <br/> extra dopo l'immagine
        local html_img = '<img src="' .. clean_src .. '" width="200"/><br/>'
        table.insert(new_content, pandoc.RawBlock('html', html_img))
      
      elseif block.t == "Para" and pandoc.utils.stringify(block):match("Relazione di progetto") then
        -- Ridotto margine inferiore H1 e margine superiore H3. Rimossi br finali.
        local title_html = '<h1 style="margin-bottom:0; padding-bottom:5px;">Relazione di progetto Tenuta al Morer</h1><h3 style="margin-top:0; padding-top:0; margin-bottom:10px;">Corso di Tecnologie Web A.A. 2025-26</h3>'
        table.insert(new_content, pandoc.RawBlock('html', title_html))

      elseif block.t == "Para" then
        local text_content = pandoc.utils.stringify(block)
        
        if text_content:match("Autori") or text_content:match("Credenziali") or text_content:match("Sito web") or text_content:match("Repository") then
           local inner_html = format_with_breaks(block.content)
           
           -- Stile aggressivo: margin-top per separare i blocchi, margin-bottom:0 per unire al testo
           local h_style = 'style="margin-bottom: 0; margin-top: 15px; font-size: 1.17em; font-weight: bold;"'
           
           inner_html = inner_html:gsub("<strong>Autori</strong>", '<h3 ' .. h_style .. '>Autori</h3>')
           inner_html = inner_html:gsub("<strong>Credenziali</strong>", '<h3 ' .. h_style .. '>Credenziali</h3>')
           inner_html = inner_html:gsub("<strong>Sito web</strong>", '<h3 ' .. h_style .. '>Sito web</h3>')
           inner_html = inner_html:gsub("<strong>Repository GitHub</strong>", '<h3 ' .. h_style .. '>Repository GitHub</h3>')
           
           -- Rimuove qualsiasi <br/> o spazio seguito da <br/> subito dopo la chiusura del titolo
           inner_html = inner_html:gsub("</h3>%s*<br/>", "</h3>")

           table.insert(new_content, pandoc.RawBlock('html', '<div>' .. inner_html .. '</div>'))
        else
           table.insert(new_content, block)
        end
      end
    end

    table.insert(new_content, pandoc.RawBlock('html', '</div><br/><hr/><br/>'))
    return new_content
  end
end

function RawBlock(el)
  if el.text:match("\\tableofcontents") or el.text:match("\\newpage") then return {} end
end

function LaTeX(el)
  if el.text:match("\\newpage") then return {} end
end

function Header(el)
  el.level = el.level + 1
  return el
end

function BlockQuote(el)
  if #el.content == 1 and el.content[1].t == 'CodeBlock' then
    return el.content[1]
  end
  return el
end